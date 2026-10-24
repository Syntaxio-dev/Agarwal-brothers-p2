<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesExpense;
use Illuminate\Support\Facades\Storage;

class BillTest extends SalesTestCase
{
    private function jpeg(int $extra = 20): string
    {
        return "\xFF\xD8\xFF\xE0" . random_bytes($extra);
    }

    private function upload(array $headers, string $uuid, string $bytes)
    {
        return $this->postJson('/api/sales/v1/uploads/bill', ['expense_uuid' => $uuid, 'data_base64' => base64_encode($bytes)], $headers);
    }

    private function pushed(array $headers): array
    {
        $e = $this->expense(['has_bill' => true]);
        $this->push($headers, ['expenses' => [$e]])->assertOk();

        return $e;
    }

    public function test_a_real_picture_is_stored_privately_and_comes_back(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $profile = $this->rep();
        $headers = $this->headers($profile);
        $e = $this->pushed($headers);
        $bytes = $this->jpeg();

        $this->upload($headers, $e['uuid'], $bytes)->assertOk()->assertJsonPath('status', 'stored');

        $expense = SalesExpense::first();
        $this->assertSame("sales/bills/{$profile->user_id}/{$e['uuid']}.jpg", $expense->bill_path);
        $this->assertSame('image/jpeg', $expense->bill_mime);
        Storage::disk('local')->assertExists($expense->bill_path);
        $this->assertSame([], Storage::disk('public')->allFiles(), 'bills must never go to the public disk');

        $back = $this->getJson("/api/sales/v1/expenses/{$e['uuid']}/bill", $headers)->assertOk();
        $this->assertSame($bytes, base64_decode($back->json('data_base64')));
        $this->assertSame('image/jpeg', $back->json('mime'));
    }

    public function test_png_and_webp_are_accepted_and_other_things_are_not(): void
    {
        Storage::fake('local');
        $headers = $this->headers($this->rep());
        $e = $this->pushed($headers);

        $this->upload($headers, $e['uuid'], "\x89PNG\r\n\x1A\n" . random_bytes(10))->assertOk();
        $this->assertStringEndsWith('.png', SalesExpense::first()->bill_path);

        $this->upload($headers, $e['uuid'], 'RIFF1234WEBPVP8 ' . random_bytes(10))->assertOk();
        $this->assertStringEndsWith('.webp', SalesExpense::first()->refresh()->bill_path);
        $this->assertCount(1, Storage::disk('local')->allFiles(), 'the earlier picture was replaced, not kept');

        foreach (['<html><script>alert(1)</script></html>', 'GIF89a......', '%PDF-1.4 ...', '<?php echo 1;', ''] as $junk) {
            $this->upload($headers, $e['uuid'], $junk)->assertStatus(422);
        }
        $this->postJson('/api/sales/v1/uploads/bill', ['expense_uuid' => $e['uuid'], 'data_base64' => '***not base64***'], $headers)->assertStatus(422);
    }

    public function test_a_picture_named_jpg_but_not_a_picture_is_refused(): void
    {
        Storage::fake('local');
        $headers = $this->headers($this->rep());
        $e = $this->pushed($headers);

        $this->postJson('/api/sales/v1/uploads/bill', [
            'expense_uuid' => $e['uuid'],
            'mime' => 'image/jpeg',
            'filename' => 'bill.jpg',
            'data_base64' => base64_encode('<script>steal()</script>'),
        ], $headers)->assertStatus(422);

        $this->assertNull(SalesExpense::first()->bill_path);
    }

    public function test_too_big_a_picture_is_refused(): void
    {
        Storage::fake('local');
        $headers = $this->headers($this->rep());
        $e = $this->pushed($headers);

        $big = "\xFF\xD8\xFF" . str_repeat('a', config('sales.bill_max_kb') * 1024);
        $this->upload($headers, $e['uuid'], $big)->assertStatus(413);
        $this->upload($headers, $e['uuid'], "\xFF\xD8\xFF" . str_repeat('a', 9 * 1024 * 1024))->assertStatus(422);   // refused before it is even decoded

        $this->assertNull(SalesExpense::first()->bill_path);
    }

    public function test_only_the_owner_can_upload_or_read_a_bill(): void
    {
        Storage::fake('local');
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $e = $this->pushed($a);
        $this->upload($a, $e['uuid'], $this->jpeg())->assertOk();

        $this->upload($b, $e['uuid'], $this->jpeg())->assertStatus(404);
        $this->getJson("/api/sales/v1/expenses/{$e['uuid']}/bill", $b)->assertStatus(404);
        $this->getJson("/api/sales/v1/expenses/{$e['uuid']}/bill")->assertStatus(401);
    }

    public function test_a_bill_cannot_change_after_the_review_and_needs_the_expense_first(): void
    {
        Storage::fake('local');
        $headers = $this->headers($this->rep());
        $e = $this->pushed($headers);

        $this->upload($headers, (string) \Illuminate\Support\Str::uuid(), $this->jpeg())->assertStatus(404);

        SalesExpense::first()->approve($this->manager());
        $this->upload($headers, $e['uuid'], $this->jpeg())->assertStatus(409)->assertJsonPath('code', 'LOCKED');
    }

    public function test_a_manager_can_see_a_bill_in_the_admin_and_nobody_else_can(): void
    {
        Storage::fake('local');
        $profile = $this->rep();
        $headers = $this->headers($profile);
        $e = $this->pushed($headers);
        $this->upload($headers, $e['uuid'], $bytes = $this->jpeg())->assertOk();

        $url = route('sales.bill', $e['uuid']);

        $this->get($url)->assertForbidden();                                                     // not signed in
        $this->actingAs($profile->user)->get($url)->assertForbidden();                            // a rep
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'sales', 'is_active' => true]))->get($url)->assertForbidden();   // office enquiry staff
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'editor', 'is_active' => true]))->get($url)->assertForbidden();

        $response = $this->actingAs($this->manager())->get($url)->assertOk();
        $this->assertSame('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));

        $this->actingAs($this->manager())->get(route('sales.bill', (string) \Illuminate\Support\Str::uuid()))->assertNotFound();
    }
}
