<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Rep\Pages\RepLogin;
use App\Sales\Rep\Resources\MyEnquiries\Pages\CreateMyEnquiry;
use App\Sales\Rep\Resources\MyEnquiries\Pages\EditMyEnquiry;
use App\Sales\Rep\Resources\MyEnquiries\Pages\ListMyEnquiries;
use App\Sales\Support\RepEnquiryService;
use App\Sales\Support\SalesInsights;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

/** The reps' own desk on the desktop: the app's sign-in, only his own data, the phone's rules, and what he does reaches the phone. */
class RepDeskTest extends SalesTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('rep');
    }

    private function enquiryOf(int $userId, array $over = []): SalesEnquiry
    {
        return SalesEnquiry::create(array_merge([
            'uuid' => (string) Str::uuid(), 'user_id' => $userId, 'client_name' => 'JK Cement', 'status' => 'qualification',
            'items' => [['product' => 'Muffle Furnace', 'brands' => '', 'quantity' => 1, 'budget_paise' => 1500000]],
            'opened_at' => now()->subDays(2), 'next_followup_at' => now()->addDay(), 'client_updated_at' => now()->subDay(),
            'timeline' => [['at' => now()->subDays(2)->utc()->format('Y-m-d\TH:i:s\Z'), 'text' => 'Enquiry opened', 'status' => 'qualification']],
        ], $over));
    }

    private function clientOf(int $userId, string $name = 'JK Cement'): SalesClient
    {
        return SalesClient::create(['name' => $name, 'city' => 'Jaipur', 'user_id' => $userId]);
    }

    // ---- signing in ---------------------------------------------------------------------------------------------------------------

    public function test_a_rep_signs_in_with_his_employee_code_or_phone_and_the_app_password(): void
    {
        $rep = $this->rep('AB-0001', '9000000001');

        foreach (['AB-0001', 'ab-0001', '9000000001', '+91 90000 00001'] as $login) {
            auth()->logout();
            Livewire::test(RepLogin::class)->fillForm(['email' => $login, 'password' => self::PASSWORD])->call('authenticate')->assertHasNoFormErrors();
            $this->assertSame($rep->user_id, auth()->id(), "signed in as $login");
        }
    }

    public function test_a_wrong_password_an_unknown_rep_a_switched_off_rep_and_a_manager_cannot_sign_in(): void
    {
        $rep = $this->rep('AB-0001', '9000000001');
        $off = $this->rep('AB-0002', '9000000002', active: false);
        $manager = $this->manager();

        $cases = [['AB-0001', 'wrong-password'], ['AB-9999', self::PASSWORD], ['AB-0002', self::PASSWORD], [$manager->email, 'password']];
        foreach ($cases as [$login, $password]) {
            Livewire::test(RepLogin::class)->fillForm(['email' => $login, 'password' => $password])->call('authenticate')->assertHasFormErrors(['email']);
            $this->assertGuest();
        }
        $this->assertNotNull($rep->user_id.$off->user_id);
    }

    // ---- the walls ----------------------------------------------------------------------------------------------------------------

    public function test_a_rep_enters_his_desk_and_nothing_of_the_office_and_a_manager_does_not_enter_the_desk(): void
    {
        $rep = $this->rep();
        $this->actingAs($rep->user);
        $this->get('/rep/home')->assertOk()->assertSee('Needs your action');
        $this->get('/rep/enquiries')->assertOk();
        $this->get('/sales/sales-management')->assertForbidden();
        $this->get('/sales/sales-clients')->assertForbidden();
        $this->get('/admin')->assertForbidden();

        $this->actingAs($this->manager());
        $this->get('/rep/home')->assertForbidden();
    }

    public function test_a_rep_sees_and_opens_only_his_own_enquiries(): void
    {
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $mine = $this->enquiryOf($a->user_id, ['client_name' => 'Mine Co']);
        $theirs = $this->enquiryOf($b->user_id, ['client_name' => 'Theirs Co']);

        $this->actingAs($a->user);
        Livewire::test(ListMyEnquiries::class)->set('activeTab', 'all')->assertCanSeeTableRecords([$mine])->assertCanNotSeeTableRecords([$theirs]);
        $this->get('/rep/enquiries/' . $mine->id . '/edit')->assertOk();
        $this->get('/rep/enquiries/' . $theirs->id . '/edit')->assertNotFound();
        $this->assertStringNotContainsString('Theirs Co', $this->get('/rep/home')->getContent());
    }

    // ---- what he does ----------------------------------------------------------------------------------------------------------------

    public function test_a_new_enquiry_is_the_phones_kind_of_record_and_reaches_the_phone(): void
    {
        $rep = $this->rep();
        $client = $this->clientOf($rep->user_id);
        $this->actingAs($rep->user);

        Livewire::test(CreateMyEnquiry::class)->fillForm([
            'client_uuid' => $client->uuid, 'status' => 'proposal', 'source' => 'call',
            'items' => [['product' => 'Hot air oven', 'brands' => 'Labtech', 'quantity' => 2, 'budget_rupees' => 50000]],
            'next_followup_at' => now()->addDays(3)->format('Y-m-d H:i:s'), 'note' => 'Wants a quote by Friday',
        ])->call('create')->assertHasNoFormErrors();

        $e = SalesEnquiry::where('user_id', $rep->user_id)->firstOrFail();
        $this->assertSame(['JK Cement', 'proposal', 'call'], [$e->client_name, $e->status, $e->source]);
        $this->assertSame(5000000, $e->items[0]['budget_paise'], 'rupees became paise');
        $this->assertSame('Wants a quote by Friday (desktop)', $e->timeline[0]['text']);
        $this->assertNotNull($e->client_updated_at);

        // his phone gets it on its next pull
        $pulled = collect($this->getJson('/api/sales/v1/sync/pull', $this->headers($rep))->assertOk()->json('enquiries'))->pluck('uuid')->all();
        $this->assertContains($e->uuid, $pulled);
    }

    public function test_an_enquiry_cannot_be_made_for_a_client_the_rep_may_not_see_or_with_bad_data(): void
    {
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $others = $this->clientOf($b->user_id, 'Secret Co');
        $service = app(RepEnquiryService::class);
        $items = [['product' => 'Oven', 'quantity' => 1, 'budget_paise' => null]];

        foreach ([
            ['client_uuid' => $others->uuid, 'items' => $items],
            ['client_uuid' => $this->clientOf($a->user_id)->uuid, 'items' => [['product' => 'x', 'quantity' => 1]]],
            ['client_uuid' => $this->clientOf($a->user_id, 'Fine Co')->uuid, 'items' => $items, 'status' => 'maybe'],
            ['client_uuid' => $this->clientOf($a->user_id, 'Far Co')->uuid, 'items' => $items, 'next_followup_at' => now()->addYears(3)->toDateTimeString()],
        ] as $bad) {
            try {
                $service->create($a->user, $bad);
                $this->fail('should have been refused');
            } catch (ValidationException) {
                // expected
            }
        }
        $this->assertSame(0, SalesEnquiry::count());
    }

    public function test_changing_the_stage_writes_the_timeline_a_newer_version_and_the_phone_rules(): void
    {
        $rep = $this->rep();
        $e = $this->enquiryOf($rep->user_id);
        $before = $e->client_updated_at;
        $service = app(RepEnquiryService::class);

        $e = $service->move($e, $rep->user, 'proposal', 'Quote sent');
        $this->assertSame('proposal', $e->status);
        $this->assertTrue($e->client_updated_at->gt($before), 'a newer version, so the phone takes it');
        $this->assertSame(['proposal', 'Moved to Proposal / Price Quote (desktop): Quote sent'], [$e->timeline[1]['status'], $e->timeline[1]['text']]);
        $this->assertNotNull($e->next_followup_at, 'the follow-up stays while the enquiry is open');

        $e = $service->move($e, $rep->user, 'won');
        $this->assertNull($e->next_followup_at, 'closing clears the follow-up');
        $this->assertNotNull($e->closed_at);

        $e = $service->move($e, $rep->user, 'negotiation');
        $this->assertNull($e->closed_at, 'reopening clears the close time');
    }

    public function test_the_phone_is_told_the_office_is_newer_when_it_pushes_an_older_copy(): void
    {
        $rep = $this->rep();
        $h = $this->headers($rep);
        $e = $this->enquiryOf($rep->user_id);
        app(RepEnquiryService::class)->move($e, $rep->user, 'proposal');

        $stale = [
            'uuid' => $e->uuid, 'client_name' => 'JK Cement', 'status' => 'lost',
            'items' => [['product' => 'Muffle Furnace', 'brands' => '', 'quantity' => 1, 'budget_paise' => null]],
            'opened_at' => $this->moment('-3 days'), 'next_followup_at' => null, 'closed_at' => $this->moment('-1 hour'), 'timeline' => [],
            'updated_at' => $this->moment('-1 hour'),
        ];
        $this->push($h, ['enquiries' => [$stale]])->assertOk();

        $this->assertSame('proposal', $e->fresh()->status, 'the desktop change is not overwritten by an older phone copy');
    }

    public function test_follow_up_and_notes_and_the_two_year_cap(): void
    {
        $rep = $this->rep();
        $e = $this->enquiryOf($rep->user_id);
        $service = app(RepEnquiryService::class);

        $e = $service->followup($e, $rep->user, now()->addDays(5)->toDateTimeString(), 'Call after the demo');
        $this->assertTrue($e->next_followup_at->gt(now()->addDays(4)));
        $this->assertStringContainsString('Call after the demo', $e->timeline[1]['text']);

        $e = $service->note($e, $rep->user, 'Sent the brochure');
        $this->assertSame('Sent the brochure (desktop)', $e->timeline[2]['text']);

        $this->expectException(ValidationException::class);
        $service->followup($e, $rep->user, now()->addYears(3)->toDateTimeString());
    }

    public function test_a_rep_cannot_change_another_reps_enquiry_through_the_service(): void
    {
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $theirs = $this->enquiryOf($b->user_id);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(RepEnquiryService::class)->move($theirs, $a->user, 'lost');
    }

    public function test_the_edit_form_saves_through_the_service(): void
    {
        $rep = $this->rep();
        $client = $this->clientOf($rep->user_id);
        $e = $this->enquiryOf($rep->user_id, ['client_uuid' => $client->uuid]);
        $this->actingAs($rep->user);

        Livewire::test(EditMyEnquiry::class, ['record' => $e->getRouteKey()])
            ->assertFormSet(['status' => 'qualification'])
            ->fillForm(['status' => 'need_analysis', 'note' => 'Visited the lab'])
            ->call('save')->assertHasNoFormErrors();

        $e = $e->fresh();
        $this->assertSame('need_analysis', $e->status);
        $lines = $e->timeline;
        $this->assertSame('Moved to Need Analysis (desktop): Visited the lab', $lines[array_key_last($lines)]['text']);
    }

    public function test_home_counts_what_needs_action(): void
    {
        $rep = $this->rep();
        $this->enquiryOf($rep->user_id, ['client_name' => 'Late Co', 'next_followup_at' => now()->subDays(2)]);
        $this->enquiryOf($rep->user_id, ['client_name' => 'Soon Co', 'next_followup_at' => now()->addDays(2)]);
        $this->enquiryOf($rep->user_id, ['client_name' => 'Closed Co', 'status' => 'won', 'closed_at' => now(), 'next_followup_at' => null]);
        $this->enquiryOf($this->rep('AB-0002', '9000000002')->user_id, ['client_name' => 'Other Co', 'next_followup_at' => now()->subDays(5)]);

        $desk = SalesInsights::repDesk($rep->user_id);

        $this->assertSame([2, 1, 1], [$desk['open'], $desk['overdue'], $desk['week']]);
        $this->assertSame(['Late Co'], $desk['needs']->map(fn ($r) => $r['e']->client_name)->all());
        $this->assertSame(1, $desk['won_month']);
    }
}
