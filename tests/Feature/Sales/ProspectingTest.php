<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Pages\SalesManagement;
use App\Sales\Filament\Resources\SalesBugReports\Pages\ListSalesBugReports;
use App\Sales\Filament\Resources\SalesProspects\Pages\ListSalesProspects;
use App\Sales\Models\SalesBugReport;
use App\Sales\Models\SalesProspect;
use App\Sales\Models\SalesProspectNote;
use App\Sales\Models\SalesProspectPerson;
use App\Sales\Support\RepAccount;
use App\Sales\Support\SalesInsights;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Prospecting (app v1.19): companies that do not work with us yet. One pool for the whole team: every rep reads it, any rep adds one and logs a
 * call, only the rep who added a prospect changes or deletes it. Plus "Report a bug" from the app.
 */
class ProspectingTest extends SalesTestCase
{
    private function prospect(array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'name' => 'Bagru Textiles',
            'territory' => 'Bagru',
            'address' => 'Plot 4, Industrial Area',
            'source' => 'google',
            'notes' => '',
            'people' => [['name' => 'Ayush Sharma', 'position' => 'Purchase Manager', 'phone' => '+919876543210', 'email' => 'ayush@example.com', 'linkedin' => 'https://linkedin.com/in/ayush-sharma']],
            'updated_at' => $this->moment(),
        ], $over);
    }

    private function note(string $prospectUuid, array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'prospect_uuid' => $prospectUuid,
            'outcome' => 'interested',
            'remark' => 'Wants a quote',
            'next_followup_at' => $this->moment('+2 days'),
            'created_at' => $this->moment(),
            'updated_at' => $this->moment(),
        ], $over);
    }

    private function bug(array $over = []): array
    {
        return array_merge(['uuid' => (string) Str::uuid(), 'area' => 'prospects', 'body' => 'The territory list is empty after a sync', 'app_version' => '1.19', 'updated_at' => $this->moment()], $over);
    }

    private function state($response, string $kind, string $uuid): string
    {
        return $this->answer($response->assertOk(), $kind, $uuid)['status'] ?? 'none';
    }

    public function test_a_prospect_with_its_people_is_saved_once_and_every_rep_sees_it(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001', name: 'Asha Verma'));
        $b = $this->headers($this->rep('REP002', '9000000002', name: 'Rohit Meena'));
        $p = $this->prospect(['people' => [['name' => 'Ayush Sharma', 'phone' => '+919876543210', 'email' => 'ayush@example.com', 'linkedin' => 'linkedin.com/in/ayush-sharma?utm_source=x']]]);

        $this->assertSame('accepted', $this->state($this->push($a, ['prospects' => [$p]]), 'prospects', $p['uuid']));
        $this->assertSame('duplicate', $this->state($this->push($a, ['prospects' => [$p]]), 'prospects', $p['uuid']));
        $this->assertSame(1, SalesProspect::count());

        $stored = SalesProspect::first();
        $this->assertSame('bagrutextiles', $stored->name_key);
        $this->assertSame('bagru', $stored->territory_key);
        $this->assertSame('https://linkedin.com/in/ayush-sharma', $stored->people->first()->linkedin, 'only a clean linkedin.com address is stored');

        // the other rep gets it, with who added it
        $pulled = collect($this->getJson('/api/sales/v1/sync/pull', $b)->assertOk()->json('prospects'))->firstWhere('uuid', $p['uuid']);
        $this->assertSame('Bagru', $pulled['territory']);
        $this->assertSame('REP001', $pulled['created_by_code']);
        $this->assertSame('Asha Verma', $pulled['created_by_name']);
        $this->assertSame('+919876543210', $pulled['people'][0]['phone']);
        $this->assertFalse($pulled['deleted']);
    }

    public function test_a_company_is_saved_once_per_territory_and_a_number_once_in_the_pool(): void
    {
        $a = $this->headers($this->rep());
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $first = $this->prospect();
        $this->push($a, ['prospects' => [$first]])->assertOk();

        // the same company in the same territory, typed another way, by another rep
        $twin = $this->prospect(['name' => 'bagru  TEXTILES', 'territory' => 'BAGRU', 'people' => []]);
        $answer = $this->answer($this->push($b, ['prospects' => [$twin]])->assertOk(), 'prospects', $twin['uuid']);
        $this->assertSame('INVALID', $answer['code']);
        $this->assertStringContainsString('already saved in Bagru', $answer['message']);

        // the same name in another territory is another company
        $other = $this->prospect(['territory' => 'Behror', 'people' => []]);
        $this->assertSame('accepted', $this->state($this->push($b, ['prospects' => [$other]]), 'prospects', $other['uuid']));

        // a number that already belongs to somebody in the prospect pool
        $sameNumber = $this->prospect(['name' => 'Other Mills', 'people' => [['name' => 'Someone Else', 'phone' => '+919876543210']]]);
        $answer = $this->answer($this->push($b, ['prospects' => [$sameNumber]])->assertOk(), 'prospects', $sameNumber['uuid']);
        $this->assertSame('INVALID', $answer['code']);
        $this->assertStringContainsString('already saved under Ayush Sharma (Bagru Textiles)', $answer['message']);

        // the same number twice inside one prospect
        $twice = $this->prospect(['name' => 'Twice Mills', 'people' => [['name' => 'One Person', 'phone' => '+919123456780'], ['name' => 'Two Person', 'phone' => '+919123456780']]]);
        $this->assertSame('INVALID', $this->answer($this->push($b, ['prospects' => [$twice]])->assertOk(), 'prospects', $twice['uuid'])['code']);

        // a client's number: prospects are only for companies that do not work with us yet
        $this->push($b, ['clients' => [$this->client(['phone' => '+919112233445', 'name' => 'JK Cement'])]])->assertOk();
        $client = $this->prospect(['name' => 'Third Mills', 'people' => [['name' => 'Rohit Verma', 'phone' => '+919112233445']]]);
        $answer = $this->answer($this->push($b, ['prospects' => [$client]])->assertOk(), 'prospects', $client['uuid']);
        $this->assertSame('INVALID', $answer['code']);
        $this->assertStringContainsString('already a client', $answer['message']);

        $this->assertSame(2, SalesProspect::count());
    }

    public function test_what_does_not_belong_is_refused(): void
    {
        $h = $this->headers($this->rep());

        foreach ([
            ['name' => 'X'], ['territory' => ''], ['source' => 'telepathy'],
            ['people' => [['name' => 'A']]],
            ['people' => [['name' => 'Ayush Sharma', 'phone' => '12345']]],
            ['people' => [['name' => 'Ayush Sharma', 'email' => 'nope']]],
            ['people' => [['name' => 'Ayush Sharma', 'linkedin' => 'https://evil.example/in/x']]],
            ['people' => [['name' => 'Ayush Sharma', 'linkedin' => 'https://linkedin.com.evil.example/in/x']]],
            ['people' => [['name' => 'Ayush Sharma', 'linkedin' => 'javascript:alert(1)']]],
            ['people' => array_fill(0, 11, ['name' => 'Ayush Sharma'])],
        ] as $bad) {
            $p = $this->prospect($bad + ['name' => 'Bagru Dyes']);
            $this->assertSame('INVALID', $this->answer($this->push($h, ['prospects' => [$p]])->assertOk(), 'prospects', $p['uuid'])['code'], json_encode($bad));
        }

        $this->assertSame(0, SalesProspect::count());
        $this->assertNull(SalesProspectPerson::cleanLinkedin('https://www.linkedin.com/'));
        $this->assertSame('https://in.linkedin.com/company/bagru-textiles', SalesProspectPerson::cleanLinkedin('https://in.linkedin.com/company/bagru-textiles/?x=1'));
    }

    public function test_only_the_rep_who_added_a_prospect_changes_or_deletes_it_but_anyone_logs_a_call(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001', name: 'Asha Verma'));
        $b = $this->headers($this->rep('REP002', '9000000002', name: 'Rohit Meena'));
        $p = $this->prospect();
        $this->push($a, ['prospects' => [$p]])->assertOk();

        // the other rep cannot change or delete it
        $edit = $p + [];
        $edit['notes'] = 'hijack';
        $edit['updated_at'] = $this->moment('+1 minute');
        $this->assertSame('NOT_ALLOWED', $this->answer($this->push($b, ['prospects' => [$edit]])->assertOk(), 'prospects', $p['uuid'])['code']);
        $gone = $this->push($b, ['deleted' => ['prospects' => [$p['uuid']]]])->assertOk()->json('results.deleted.prospects.0');
        $this->assertSame('NOT_ALLOWED', $gone['code']);
        $this->assertNull(SalesProspect::first()->notes);

        // but a call by the other rep is fine, and it is what the status shows
        $n = $this->note($p['uuid']);
        $this->assertSame('accepted', $this->state($this->push($b, ['prospect_notes' => [$n]]), 'prospect_notes', $n['uuid']));
        $this->assertSame('duplicate', $this->state($this->push($b, ['prospect_notes' => [$n]]), 'prospect_notes', $n['uuid']));
        $this->assertSame('UUID_TAKEN', $this->answer($this->push($a, ['prospect_notes' => [$n]])->assertOk(), 'prospect_notes', $n['uuid'])['code']);
        $this->assertSame('Interested', SalesProspectNote::labelOf(SalesProspect::first()->calls()->first()->outcome));

        // everybody sees the call and who made it
        $pulled = collect($this->getJson('/api/sales/v1/sync/pull', $a)->assertOk()->json('prospect_notes'))->firstWhere('uuid', $n['uuid']);
        $this->assertSame('REP002', $pulled['created_by_code']);
        $this->assertSame('interested', $pulled['outcome']);

        // the owner changes it
        $mine = $p;
        $mine['notes'] = 'Called twice';
        $mine['updated_at'] = $this->moment('+1 minute');
        $this->assertSame('accepted', $this->state($this->push($a, ['prospects' => [$mine]]), 'prospects', $p['uuid']));
        $this->assertSame('Called twice', SalesProspect::first()->notes);
    }

    public function test_a_call_needs_a_real_outcome_and_a_prospect_that_is_still_there(): void
    {
        $h = $this->headers($this->rep());
        $p = $this->prospect();
        $this->push($h, ['prospects' => [$p]])->assertOk();

        foreach ([['outcome' => 'maybe'], ['remark' => str_repeat('x', 301)], ['next_followup_at' => $this->moment('+3 years')], ['prospect_uuid' => 'nope']] as $bad) {
            $n = $this->note($p['uuid'], $bad);
            $this->assertSame('INVALID', $this->answer($this->push($h, ['prospect_notes' => [$n]])->assertOk(), 'prospect_notes', $n['uuid'])['code'], json_encode($bad));
        }

        $orphan = $this->note((string) Str::uuid());
        $this->assertSame('NOT_ALLOWED', $this->answer($this->push($h, ['prospect_notes' => [$orphan]])->assertOk(), 'prospect_notes', $orphan['uuid'])['code']);
        $this->assertSame(0, SalesProspectNote::count());
    }

    public function test_deleting_a_prospect_reaches_the_other_rep_and_its_calls_are_not_sent_any_more(): void
    {
        $a = $this->headers($this->rep());
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $p = $this->prospect();
        $n = $this->note($p['uuid']);
        $this->push($a, ['prospects' => [$p], 'prospect_notes' => [$n]])->assertOk();
        $this->assertCount(1, $this->getJson('/api/sales/v1/sync/pull', $b)->json('prospect_notes'));

        $this->assertSame('accepted', $this->push($a, ['deleted' => ['prospects' => [$p['uuid']]]])->assertOk()->json('results.deleted.prospects.0.status'));
        $this->assertTrue(SalesProspect::withTrashed()->first()->trashed());

        $pull = $this->getJson('/api/sales/v1/sync/pull', $b)->assertOk();
        $this->assertTrue(collect($pull->json('prospects'))->firstWhere('uuid', $p['uuid'])['deleted']);
        $this->assertSame([], $pull->json('prospect_notes'));

        // the same company can be added again after it was deleted
        $again = $this->prospect(['people' => []]);
        $this->assertSame('accepted', $this->state($this->push($b, ['prospects' => [$again]]), 'prospects', $again['uuid']));
    }

    public function test_a_bug_report_is_kept_once_and_never_comes_back_to_a_phone(): void
    {
        $a = $this->headers($this->rep());
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $bug = $this->bug();

        $this->assertSame('accepted', $this->state($this->push($a, ['bug_reports' => [$bug]]), 'bug_reports', $bug['uuid']));
        $this->assertSame('duplicate', $this->state($this->push($a, ['bug_reports' => [$bug]]), 'bug_reports', $bug['uuid']));
        $this->assertSame('UUID_TAKEN', $this->answer($this->push($b, ['bug_reports' => [$bug]])->assertOk(), 'bug_reports', $bug['uuid'])['code']);
        $this->assertSame(1, SalesBugReport::count());
        $this->assertSame('1.19', SalesBugReport::first()->app_version);

        foreach ([['body' => 'short'], ['area' => 'nowhere'], ['body' => str_repeat('x', 1001)]] as $bad) {
            $x = $this->bug($bad);
            $this->assertSame('INVALID', $this->answer($this->push($a, ['bug_reports' => [$x]])->assertOk(), 'bug_reports', $x['uuid'])['code'], json_encode($bad));
        }

        $this->assertArrayNotHasKey('bug_reports', $this->getJson('/api/sales/v1/sync/pull', $a)->assertOk()->json());
    }

    public function test_a_rep_cannot_pile_up_bug_reports(): void
    {
        $h = $this->headers($this->rep());
        foreach (collect(range(1, 31))->map(fn () => $this->bug())->chunk(10) as $chunk) {
            $this->push($h, ['bug_reports' => $chunk->values()->all()])->assertOk();
        }

        $this->assertSame(30, SalesBugReport::count());
    }

    public function test_the_manager_reads_the_pool_and_answers_bug_reports_but_a_rep_cannot_open_the_screens(): void
    {
        $profile = $this->rep('REP001', '9000000001', name: 'Asha Verma');
        $h = $this->headers($profile);
        $p = $this->prospect();
        $this->push($h, ['prospects' => [$p], 'prospect_notes' => [$this->note($p['uuid'])], 'bug_reports' => [$this->bug()]])->assertOk();

        $this->actingAs($this->manager());
        $prospect = SalesProspect::first();

        Livewire::test(ListSalesProspects::class)
            ->assertCanSeeTableRecords([$prospect])
            ->assertSee('Bagru Textiles')
            ->assertSee('Interested')
            ->filterTable('territory', 'Bagru')
            ->assertCanSeeTableRecords([$prospect])
            ->filterTable('status', 'no_answer')
            ->assertCanNotSeeTableRecords([$prospect])
            ->resetTableFilters()
            ->mountTableAction('details', $prospect);

        // what the details popup shows: the people and every call, as plain text
        $html = view('filament.sales.prospect-details', ['prospect' => $prospect->load(['people', 'calls.user.salesProfile', 'addedBy'])])->render();
        $this->assertStringContainsString('Ayush Sharma', $html);
        $this->assertStringContainsString('https://linkedin.com/in/ayush-sharma', $html);
        $this->assertStringContainsString('Wants a quote', $html);
        $this->assertStringContainsString('Interested', $html);

        $report = SalesBugReport::first();
        Livewire::test(ListSalesBugReports::class)
            ->assertCanSeeTableRecords([$report])
            ->callTableAction('fixed', $report, data: ['reply' => 'Fixed in the next version.'])
            ->assertHasNoTableActionErrors();
        $this->assertSame('fixed', $report->fresh()->status);
        $this->assertSame('Fixed in the next version.', $report->fresh()->reply);

        Livewire::test(ListSalesProspects::class)->callTableAction('delete', $prospect);
        $this->assertTrue($prospect->fresh()->trashed());

        // a rep (the phone only) is not allowed into the admin screens
        $this->assertFalse($profile->user->can('viewAny', SalesProspect::class));
        $this->assertFalse($profile->user->can('viewAny', SalesBugReport::class));
    }

    public function test_the_management_page_shows_the_prospecting_card(): void
    {
        $a = $this->headers($this->rep());
        $one = $this->prospect();
        $two = $this->prospect(['name' => 'Behror Mills', 'territory' => 'Behror', 'people' => []]);
        $three = $this->prospect(['name' => 'Bagru Dyes', 'people' => []]);
        $this->push($a, ['prospects' => [$one, $two, $three], 'prospect_notes' => [$this->note($one['uuid']), $this->note($two['uuid'], ['outcome' => 'converted'])], 'bug_reports' => [$this->bug()]])->assertOk();

        $figures = SalesInsights::prospecting(30);
        $this->assertSame(3, $figures['total']);
        $this->assertSame(1, $figures['untouched']);
        $this->assertSame(2, $figures['calls']);
        $this->assertSame(1, $figures['interested']);
        $this->assertSame(1, $figures['converted']);
        $this->assertSame(1, $figures['bugs_open']);
        $this->assertSame(['name' => 'Bagru', 'count' => 2], $figures['territories'][0]);

        $this->actingAs($this->manager());
        Livewire::test(SalesManagement::class)->assertSee('Prospecting')->assertSee('Prospects in the pool');
    }

    public function test_deleting_a_rep_hands_the_shared_prospects_and_their_calls_to_the_manager(): void
    {
        $profile = $this->rep();
        $h = $this->headers($profile);
        $p = $this->prospect();
        $this->push($h, ['prospects' => [$p], 'prospect_notes' => [$this->note($p['uuid'])], 'bug_reports' => [$this->bug()]])->assertOk();

        $manager = $this->manager();
        $profile->update(['is_active' => false]);
        RepAccount::deleteWithRecords($profile->fresh(), $manager);

        $this->assertSame(1, SalesProspect::count());
        $this->assertSame($manager->id, SalesProspect::first()->user_id);
        $this->assertSame($manager->id, SalesProspectNote::first()->user_id);
        $this->assertSame(0, SalesBugReport::count());
    }

    public function test_a_visit_planned_to_a_prospect_keeps_its_link_and_an_older_phone_never_wipes_it(): void
    {
        $h = $this->headers($this->rep());
        $p = $this->prospect();
        $this->push($h, ['prospects' => [$p]])->assertOk();

        $meeting = $this->meeting(['client_name' => 'Bagru Textiles', 'prospect_uuid' => $p['uuid'], 'status' => 'planned', 'scheduled_at' => $this->moment('+1 day'), 'meeting_at' => $this->moment('+1 day')]);
        $this->assertSame('accepted', $this->state($this->push($h, ['meetings' => [$meeting]]), 'meetings', $meeting['uuid']));
        $this->assertSame($p['uuid'], \App\Sales\Models\SalesMeeting::first()->prospect_uuid);

        $pulled = collect($this->getJson('/api/sales/v1/sync/pull', $h)->assertOk()->json('meetings'))->firstWhere('uuid', $meeting['uuid']);
        $this->assertSame($p['uuid'], $pulled['prospect_uuid']);

        // a later edit from a phone that does not send the key keeps the link
        $edit = $meeting;
        unset($edit['prospect_uuid']);
        $edit['purpose'] = 'Price talk';
        $edit['updated_at'] = $this->moment('+1 minute');
        $this->push($h, ['meetings' => [$edit]])->assertOk();
        $this->assertSame($p['uuid'], \App\Sales\Models\SalesMeeting::first()->prospect_uuid);

        // a bad id is refused
        $bad = $this->meeting(['prospect_uuid' => 'nope']);
        $this->assertSame('INVALID', $this->answer($this->push($h, ['meetings' => [$bad]])->assertOk(), 'meetings', $bad['uuid'])['code']);
    }
}
