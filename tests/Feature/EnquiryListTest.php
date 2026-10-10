<?php

namespace Tests\Feature;

use App\Mail\NewEnquiry;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EnquiryListTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, bool $brandActive = true): Product
    {
        $brand = Brand::create(['name' => 'Brand ' . $name, 'slug' => 'b-' . uniqid(), 'is_active' => $brandActive]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Ovens', 'slug' => 'c-' . uniqid()]);

        return Product::create(['category_id' => $cat->id, 'name' => $name, 'slug' => 'p-' . uniqid(), 'is_active' => true]);
    }

    private function payload(array $products, array $extra = []): array
    {
        return array_merge([
            'name' => 'Ravi Sharma', 'email' => 'ravi@example.com', 'phone' => '9999999999', 'company' => 'Sharma Labs',
            'items' => collect($products)->map(fn ($p, $i) => ['slug' => $p->slug, 'qty' => $i + 2])->values()->all(),
        ], $extra);
    }

    public function test_page_loads(): void
    {
        $this->get(route('enquiry-list'))->assertOk();
    }

    public function test_group_enquiry_is_saved_with_items_and_mailed(): void
    {
        $oven = $this->product('Hot Air Oven');
        $chiller = $this->product('Chiller');

        $this->post(route('enquiry-list.store'), $this->payload([$oven, $chiller]))
            ->assertRedirect(route('enquiry-list'))->assertSessionHas('enquiry_sent', 2);

        $enquiry = Enquiry::with('items')->firstOrFail();
        $this->assertSame('Sharma Labs', $enquiry->company);
        $this->assertSame(2, $enquiry->items->count());
        $this->assertSame(['Hot Air Oven' => 2, 'Chiller' => 3], $enquiry->items->pluck('quantity', 'product_name')->all());

        Mail::assertQueued(NewEnquiry::class, 1);
    }

    public function test_office_email_lists_products_with_serial_numbers(): void
    {
        $oven = $this->product('Hot Air Oven');
        $chiller = $this->product('Chiller');
        $this->post(route('enquiry-list.store'), $this->payload([$oven, $chiller]));

        $enquiry = Enquiry::firstOrFail();
        $mail = new NewEnquiry($enquiry);
        $html = $mail->render();

        $mail->assertSeeInHtml('New group enquiry');
        $mail->assertSeeInHtml('Hot Air Oven');
        $mail->assertSeeInHtml('Chiller');
        $mail->assertSeeInHtml('Total units');
        $this->assertStringContainsString('Sr.', $html);
        $this->assertStringContainsString('Sharma Labs', $html);
        $this->assertSame('New group enquiry: 2 products from Ravi Sharma', $mail->build()->subject);
    }

    public function test_single_product_enquiry_email_still_works(): void
    {
        $p = $this->product('Solo');
        $this->post(route('enquiry.store'), ['product_id' => $p->id, 'name' => 'Asha', 'email' => 'a@example.com']);

        $mail = new NewEnquiry(Enquiry::firstOrFail());
        $mail->assertSeeInHtml('New product enquiry');
        $mail->assertSeeInHtml('Solo');
    }

    public function test_hidden_products_empty_lists_and_bad_input_are_rejected(): void
    {
        $ok = $this->product('Visible');
        $hidden = $this->product('Hidden', brandActive: false);

        // Nothing valid left
        $this->post(route('enquiry-list.store'), $this->payload([$hidden]))->assertSessionHasErrors('items');
        // Empty list, honeypot, bad quantity, bad slug
        $this->post(route('enquiry-list.store'), ['name' => 'Asha', 'email' => 'a@example.com'])->assertSessionHasErrors('items');
        $this->post(route('enquiry-list.store'), $this->payload([$ok], ['website' => 'spam']))->assertSessionHasErrors('website');
        $this->post(route('enquiry-list.store'), ['name' => 'Asha', 'email' => 'a@example.com', 'items' => [['slug' => $ok->slug, 'qty' => 0]]])->assertSessionHasErrors('items.0.qty');
        $this->post(route('enquiry-list.store'), ['name' => 'Asha', 'email' => 'a@example.com', 'items' => [['slug' => '../x', 'qty' => 1]]])->assertSessionHasErrors('items.0.slug');

        $this->assertSame(0, Enquiry::count());
        Mail::assertNothingQueued();
    }

    public function test_hidden_products_are_dropped_from_a_mixed_list(): void
    {
        $ok = $this->product('Visible');
        $hidden = $this->product('Hidden', brandActive: false);

        $this->post(route('enquiry-list.store'), $this->payload([$ok, $hidden]))->assertSessionHas('enquiry_sent', 1);
        $this->assertSame(['Visible'], Enquiry::firstOrFail()->items->pluck('product_name')->all());
    }

    public function test_more_than_twenty_products_are_rejected(): void
    {
        $items = collect(range(1, 21))->map(fn ($i) => ['slug' => 'p-' . $i, 'qty' => 1])->all();

        $this->post(route('enquiry-list.store'), ['name' => 'Asha', 'email' => 'a@example.com', 'items' => $items])->assertSessionHasErrors('items');
    }

    public function test_buttons_are_on_cards_and_admin_shows_group_enquiries(): void
    {
        $oven = $this->product('Hot Air Oven');
        $this->get(route('product.show', $oven->slug))->assertOk()->assertSee('data-item', false);
        $this->get(route('brand.show', $oven->category->brand->slug))->assertOk()->assertSee('data-item', false);

        $this->post(route('enquiry-list.store'), $this->payload([$oven]));
        $enquiry = Enquiry::firstOrFail();

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $this->get('/admin/enquiries')->assertOk()->assertSee('1 products');
        $this->get("/admin/enquiries/{$enquiry->id}/edit")->assertOk()->assertSee('Hot Air Oven');
    }
}
