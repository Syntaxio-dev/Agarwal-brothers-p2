<?php

namespace Tests\Feature\Sales;

use App\Models\User;
use App\Sales\Actions\MoveEnquiryStage;
use App\Sales\Filament\Resources\SalesEnquiries\Pages\ListSalesEnquiries;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\SalesInsights;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * The Enquiries pipeline board (design 5a): urgency (the phone's rules), the board numbers, and the office moving an enquiry to another stage
 * (drag and drop, "Move to...", keyboard) without ever overwriting a newer change from the phone. 2026-10-26 is a Monday.
 */
class EnquiryBoardTest extends SalesTestCase
{
    private function viewerOf(array $access): User
    {
        config(['roles.test_viewer' => ['label' => 'Test viewer', 'description' => 'x', 'access' => $access]]);

        return User::factory()->create(['role' => 'test_viewer', 'is_active' => true]);
    }

    private function at(string $dateTime): CarbonImmutable
    {
        return CarbonImmutable::parse($dateTime, config('sales.timezone'));
    }

    private function enquiry(SalesProfile $rep, array $over = []): SalesEnquiry
    {
        return SalesEnquiry::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'user_id' => $rep->user_id,
            'client_name' => 'Sharma Scientific',
            'status' => 'price_sent',
            'items' => [['product' => 'Muffle Furnace', 'brands' => '', 'quantity' => 1, 'budget_paise' => 15000000]],
            'opened_at' => now()->subDays(10),
            'next_followup_at' => now()->addDays(2),
            'timeline' => [['at' => now()->subDays(9)->format('Y-m-d\TH:i:s\Z'), 'text' => 'Enquiry opened', 'status' => 'new']],
            'client_updated_at' => now()->subHour(),
        ], $over));
    }

    private function move(SalesEnquiry $e, string $to, ?User $by = null, ?string $note = null, $version = null, ?string $from = null): array
    {
        return app(MoveEnquiryStage::class)->handle($e, $to, $by ?? $this->manager(), $note, $version ?? $e->client_updated_at, $from);
    }

    // ---- urgency: the same answers as urgency() on the phone -----------------------------------------------------------

    public function test_urgency_follows_the_phones_rules_in_business_time(): void
    {
        $rep = $this->rep();
        $now = $this->at('2026-10-26 15:00');
        $u = fn (array $over) => SalesInsights::enquiryUrgency($this->enquiry($rep, $over), $now);

        $this->assertSame('overdue', $u(['next_followup_at' => $this->at('2026-10-20 16:00')->utc()])['key']);
        $this->assertSame('6 days late', $u(['next_followup_at' => $this->at('2026-10-20 16:00')->utc()])['label']);
        $this->assertSame('Late today', $u(['next_followup_at' => $this->at('2026-10-26 13:00')->utc()])['label'], 'due earlier today');
        $this->assertSame('overdue', $u(['next_followup_at' => $now->utc()])['key'], 'the moment itself has passed (the phone: at <= now)');

        $today = $u(['next_followup_at' => $this->at('2026-10-26 16:30')->utc()]);
        $this->assertSame(['today', 'Today 4:30 pm'], [$today['key'], $today['label']]);
        $this->assertSame('today', $u(['next_followup_at' => $this->at('2026-10-26 23:59')->utc()])['key']);
        $this->assertSame('planned', $u(['next_followup_at' => $this->at('2026-10-27 00:01')->utc()])['key'], 'a minute after midnight is another day');
        $this->assertSame('In 3 days', $u(['next_followup_at' => $this->at('2026-10-29 10:00')->utc()])['label']);

        // no follow-up: idle only after MORE than three days untouched
        $idle = $u(['next_followup_at' => null, 'client_updated_at' => $this->at('2026-10-22 10:00')->utc()]);
        $this->assertSame(['idle', 'Idle 4 days'], [$idle['key'], $idle['label']]);
        $this->assertSame('none', $u(['next_followup_at' => null, 'client_updated_at' => $this->at('2026-10-23 15:00')->utc()])['key'], 'exactly three days is not idle');
        $this->assertSame('idle', $u(['next_followup_at' => null, 'client_updated_at' => $this->at('2026-10-23 14:59')->utc()])['key']);
        $this->assertSame('none', $u(['next_followup_at' => null, 'client_updated_at' => $this->at('2026-10-26 09:00')->utc()])['key']);

        // closed: nothing is due
        $this->assertSame('none', $u(['status' => 'won', 'next_followup_at' => null, 'client_updated_at' => $this->at('2026-10-01 10:00')->utc()])['key']);

        // around midnight: 23:50 on the 26th and 00:10 on the 27th are different days in India
        $e = $this->enquiry($rep, ['next_followup_at' => $this->at('2026-10-26 23:50')->utc()]);
        $this->assertSame('today', SalesInsights::enquiryUrgency($e, $this->at('2026-10-26 23:40'))['key']);
        $this->assertSame('overdue', SalesInsights::enquiryUrgency($e, $this->at('2026-10-27 00:10'))['key']);
        $this->assertSame('1 day late', SalesInsights::enquiryUrgency($e, $this->at('2026-10-27 00:10'))['label']);
    }

    // ---- the board ---------------------------------------------------------------------------------------------------

    public function test_the_board_counts_sums_and_orders_the_cards(): void
    {
        $a = $this->rep('AB-0001', '9000000001', name: 'Ravi');
        $b = $this->rep('AB-0002', '9000000002', name: 'Sunita');
        $now = $this->at('2026-10-26 15:00');
        $late = $this->enquiry($a, ['client_name' => 'Late Co', 'next_followup_at' => $this->at('2026-10-20 10:00')->utc(), 'items' => [['product' => 'Oven', 'quantity' => 2, 'budget_paise' => 5000000]]]);
        $today = $this->enquiry($a, ['client_name' => 'Today Co', 'next_followup_at' => $this->at('2026-10-26 18:00')->utc(), 'items' => [['product' => 'Oven', 'quantity' => 1, 'budget_paise' => 2500000]]]);
        $this->enquiry($b, ['client_name' => 'Later Co', 'next_followup_at' => $this->at('2026-10-30 10:00')->utc(), 'items' => [['product' => 'Balance', 'quantity' => 1, 'budget_paise' => 1000000]], 'source' => 'whatsapp']);
        $this->enquiry($b, ['client_name' => 'New Co', 'status' => 'new', 'next_followup_at' => null, 'client_updated_at' => $this->at('2026-10-25 10:00')->utc()]);
        $this->enquiry($b, ['client_name' => 'Won Co', 'status' => 'won', 'closed_at' => $this->at('2026-10-10 10:00')->utc(), 'next_followup_at' => null]);

        $board = SalesInsights::pipelineBoard([], 'all', 8, $now);

        $this->assertSame(['new', 'price_to_send', 'price_sent', 'callback', 'meeting_planned', 'negotiation'], array_keys($board['columns']), 'the phone\'s order, the open stages only');
        $col = $board['columns']['price_sent'];
        $this->assertSame(3, $col['count']);
        $this->assertSame(8500000, $col['paise']);
        $this->assertSame(['Late Co', 'Today Co', 'Later Co'], array_column($col['cards'], 'client'), 'overdue, then today, then the rest by follow-up date');
        $this->assertSame(4, $board['summary']['open']);
        $this->assertSame(2, $board['summary']['need'], 'overdue + due today (+ idle)');
        $this->assertSame(['open' => 4, 'overdue' => 1, 'today' => 1, 'idle' => 0], $board['counts']);
        $this->assertSame($late->client_updated_at->format('Y-m-d H:i:s.v'), $col['cards'][0]['version']);

        // filters: a rep, came through, the product names inside the items, the chips dim instead of hiding
        $this->assertSame(2, SalesInsights::pipelineBoard(['rep' => $a->user_id], 'all', 8, $now)['summary']['open']);
        $this->assertSame(1, SalesInsights::pipelineBoard(['source' => 'whatsapp'], 'all', 8, $now)['summary']['open']);
        $this->assertSame(2, SalesInsights::pipelineBoard(['q' => 'oven'], 'all', 8, $now)['summary']['open'], 'search finds a product asked for');
        $this->assertSame(1, SalesInsights::pipelineBoard(['q' => 'sunita', 'source' => 'whatsapp'], 'all', 8, $now)['summary']['open'], 'and the rep name');
        $dimmed = collect(SalesInsights::pipelineBoard([], 'overdue', 8, $now)['columns']['price_sent']['cards'])->pluck('dim', 'client');
        $this->assertSame(['Late Co' => false, 'Today Co' => true, 'Later Co' => true], $dimmed->all());
    }

    public function test_a_column_shows_eight_cards_and_says_how_many_more(): void
    {
        $rep = $this->rep();
        for ($i = 0; $i < 11; $i++) {
            $this->enquiry($rep, ['client_name' => "Client $i", 'status' => 'callback']);
        }

        $col = SalesInsights::pipelineBoard()['columns']['callback'];

        $this->assertCount(8, $col['cards']);
        $this->assertSame(11, $col['count']);
        $this->assertSame(3, $col['hidden']);
    }

    public function test_the_closed_strip_counts_the_month_and_the_win_rate(): void
    {
        $rep = $this->rep();
        $month = $this->at('2026-10-15 10:00');
        $items = [['product' => 'Oven', 'quantity' => 1, 'budget_paise' => 1000000]];

        $this->assertNull(SalesInsights::closedSummary([], $month)['rate'], 'nothing decided: no rate');

        foreach ([['won', '2026-10-01 00:30'], ['won', '2026-10-31 23:30'], ['won', '2026-10-12 10:00'], ['lost', '2026-10-05 10:00'], ['won', '2026-09-30 23:30'], ['lost', '2026-11-01 00:30']] as [$status, $when]) {
            $this->enquiry($rep, ['status' => $status, 'closed_at' => $this->at($when)->utc(), 'next_followup_at' => null, 'items' => $items]);
        }

        $s = SalesInsights::closedSummary([], $month);

        $this->assertSame(['count' => 3, 'paise' => 3000000], $s['won'], 'the first and the last day of the month count, the day before and after do not');
        $this->assertSame(1, $s['lost']['count']);
        $this->assertSame(75, $s['rate']);
        $this->assertSame('October', $s['label']);
    }

    // ---- moving an enquiry ----------------------------------------------------------------------------------------------

    public function test_an_open_enquiry_can_go_to_any_open_stage_in_any_direction(): void
    {
        $manager = $this->manager();
        $e = $this->enquiry($this->rep(), ['status' => 'price_sent']);

        $r = $this->move($e, 'new', $manager, 'Client wants a fresh quote');
        $this->assertSame('moved', $r['result']);
        $e = $e->fresh();
        $this->assertSame('new', $e->status);
        $this->assertNotNull($e->next_followup_at, 'the follow-up stays when the enquiry stays open');
        $this->assertNull($e->closed_at);

        $this->assertSame('moved', $this->move($e, 'negotiation', $manager)['result']);
        $this->assertSame('negotiation', $e->fresh()->status);
    }

    public function test_moving_to_won_or_lost_closes_and_clears_the_follow_up(): void
    {
        foreach (['won', 'lost'] as $to) {
            $e = $this->enquiry($this->rep('AB-' . strtoupper($to), $to === 'won' ? '9000000001' : '9000000002'), ['status' => 'negotiation']);
            $this->assertNotNull($e->next_followup_at);

            $this->assertSame('moved', $this->move($e, $to)['result']);

            $e = $e->fresh();
            $this->assertSame($to, $e->status);
            $this->assertNotNull($e->closed_at);
            $this->assertNull($e->next_followup_at);
        }
    }

    public function test_a_closed_enquiry_cannot_be_moved_a_same_stage_drop_is_nothing_and_an_unknown_stage_is_refused(): void
    {
        $rep = $this->rep();
        $closed = $this->enquiry($rep, ['status' => 'won', 'closed_at' => now(), 'next_followup_at' => null]);
        $open = $this->enquiry($rep, ['status' => 'callback']);

        $this->assertSame('closed', $this->move($closed, 'new')['result']);
        $this->assertSame('won', $closed->fresh()->status);

        $before = $open->client_updated_at->format('Y-m-d H:i:s.v');
        $this->assertSame('same', $this->move($open, 'callback')['result']);
        $this->assertSame('invalid', $this->move($open, 'frozen')['result']);
        $this->assertSame($before, $open->fresh()->client_updated_at->format('Y-m-d H:i:s.v'), 'nothing was written');
    }

    public function test_a_move_adds_one_timeline_entry_and_a_newer_version_and_touches_nothing_else(): void
    {
        $manager = $this->manager();
        $rep = $this->rep();
        $e = $this->enquiry($rep, ['client_updated_at' => now()->subMinutes(5), 'source' => 'call']);
        $old = $e->only(['client_name', 'contact_name', 'items', 'source', 'opened_at', 'user_id', 'next_followup_at']);
        $version = $e->client_updated_at;

        $this->move($e, 'negotiation', $manager, 'Called the client');

        $e = $e->fresh();
        $this->assertCount(2, $e->timeline);
        $entry = $e->timeline[1];
        $this->assertSame('negotiation', $entry['status']);
        $this->assertSame('Moved to Negotiation by ' . $manager->name . ' (office): Called the client', $entry['text']);
        $this->assertTrue($e->client_updated_at->gt($version), 'a newer version, so the phone takes it');
        $this->assertMatchesRegularExpression('/\.\d{3}$/', $e->client_updated_at->format('Y-m-d H:i:s.v'));
        $this->assertEquals($old, $e->only(array_keys($old)), 'items, source, client, person, opened_at, rep are untouched');
        $this->assertTrue(DB::table('activity_logs')->where('user_id', $manager->id)->where('subject_type', SalesEnquiry::class)->where('subject_id', $e->id)->where('action', 'updated')->exists());
    }

    public function test_the_timeline_keeps_the_last_100_entries_and_each_text_500_characters(): void
    {
        $entries = collect(range(1, 100))->map(fn ($i) => ['at' => now()->subDays(200 - $i)->format('Y-m-d\TH:i:s\Z'), 'text' => "note $i", 'status' => null])->all();
        $e = $this->enquiry($this->rep(), ['timeline' => $entries]);

        $r = $this->move($e, 'new', null, str_repeat('x', 900));

        $t = $e->fresh()->timeline;
        $this->assertCount(100, $t);
        $this->assertSame('note 2', $t[0]['text'], 'the oldest entry went, as on the phone');
        $this->assertSame(500, mb_strlen(end($t)['text']));

        // undo puts the dropped entry back
        $this->assertSame('undone', app(MoveEnquiryStage::class)->undo($r['undo'], $this->manager()));
        $back = $e->fresh()->timeline;
        $this->assertCount(100, $back);
        $this->assertSame('note 1', $back[0]['text']);
    }

    public function test_a_move_is_refused_when_the_phone_has_a_newer_version_or_the_stage_changed(): void
    {
        $rep = $this->rep();
        $e = $this->enquiry($rep, ['status' => 'price_sent']);
        $loaded = CarbonImmutable::parse($e->client_updated_at->format('Y-m-d H:i:s.v'), 'UTC');

        // the rep synced a change after the board loaded the card
        $e->forceFill(['client_updated_at' => $loaded->addMinutes(3)])->save();
        $r = $this->move($e, 'negotiation', null, null, $loaded);
        $this->assertSame('conflict', $r['result']);
        $this->assertSame('price_sent', $e->fresh()->status);
        $this->assertCount(1, $e->fresh()->timeline, 'nothing half written');

        // the rep moved it to another stage meanwhile
        $e->forceFill(['status' => 'callback'])->save();
        $this->assertSame('conflict', $this->move($e->fresh(), 'negotiation', null, null, $e->fresh()->client_updated_at, 'price_sent')['result']);

        // a soft-deleted enquiry is gone
        $gone = $this->enquiry($rep);
        $copy = SalesEnquiry::find($gone->id);
        $gone->delete();
        $this->assertSame('gone', $this->move($copy, 'new')['result']);
    }

    public function test_two_moves_racing_the_second_one_conflicts(): void
    {
        $e = $this->enquiry($this->rep(), ['status' => 'price_sent']);
        $loaded = $e->client_updated_at;
        $a = SalesEnquiry::find($e->id);
        $b = SalesEnquiry::find($e->id);

        $this->assertSame('moved', $this->move($a, 'negotiation', null, null, $loaded, 'price_sent')['result']);
        $this->assertSame('conflict', $this->move($b, 'new', null, null, $loaded, 'price_sent')['result'], 'the second manager still holds the old card');
        $this->assertSame('negotiation', $e->fresh()->status);
    }

    public function test_undo_restores_everything_and_refuses_when_something_changed_since(): void
    {
        $rep = $this->rep();
        $e = $this->enquiry($rep, ['status' => 'negotiation']);
        $before = $e->fresh()->only(['status', 'closed_at', 'next_followup_at', 'timeline']);

        $r = $this->move($e, 'lost');
        $this->assertSame('lost', $e->fresh()->status);
        $this->assertSame('undone', app(MoveEnquiryStage::class)->undo($r['undo'], $this->manager()));

        $after = $e->fresh();
        $this->assertSame('negotiation', $after->status);
        $this->assertNull($after->closed_at);
        $this->assertEquals($before['next_followup_at'], $after->next_followup_at);
        $this->assertEquals($before['timeline'], $after->timeline, 'exactly the appended entry is removed');

        // the phone changed the enquiry after the move: nothing is undone
        $r = $this->move($after, 'callback');
        $e->fresh()->forceFill(['client_updated_at' => now()->addMinutes(2), 'status' => 'new'])->save();
        $this->assertSame('changed', app(MoveEnquiryStage::class)->undo($r['undo'], $this->manager()));
        $this->assertSame('new', $e->fresh()->status);
    }

    // ---- the contract with the phone ----------------------------------------------------------------------------------------

    public function test_the_phone_gets_the_move_and_its_own_newer_changes_still_win(): void
    {
        $rep = $this->rep();
        $headers = $this->headers($rep);
        $uuid = (string) Str::uuid();
        $payload = fn (array $over = []) => array_merge([
            'uuid' => $uuid, 'client_name' => 'Sharma Scientific', 'status' => 'price_sent', 'source' => 'call',
            'items' => [['product' => 'Muffle Furnace', 'brands' => '', 'quantity' => 1, 'budget_paise' => 100000]],
            'opened_at' => $this->moment('-5 days'), 'next_followup_at' => $this->moment('+2 days'), 'timeline' => [], 'updated_at' => $this->moment('-1 hour'),
        ], $over);

        $this->push($headers, ['enquiries' => [$payload()]])->assertOk();
        $e = SalesEnquiry::where('uuid', $uuid)->firstOrFail();

        $this->assertSame('moved', $this->move($e, 'lost', null, 'Not interested')['result']);

        $pulled = collect($this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk()->json('enquiries'))->firstWhere('uuid', $uuid);
        $this->assertSame('lost', $pulled['status']);
        $this->assertNotNull($pulled['closed_at']);
        $this->assertNull($pulled['next_followup_at']);
        $this->assertStringContainsString('(office)', end($pulled['timeline'])['text']);
        $this->assertTrue(CarbonImmutable::parse($pulled['updated_at'])->gt(CarbonImmutable::parse($this->moment('-1 hour'))), 'newer than what the phone holds');

        // a later push from the phone with an OLDER version does not overwrite the office's move...
        $old = $this->push($headers, ['enquiries' => [$payload(['status' => 'negotiation', 'updated_at' => $this->moment('-30 minutes')])]])->assertOk();
        $this->assertSame('lost', $e->fresh()->status);
        $this->assertNotSame('accepted', $this->answer($old, 'enquiries', $uuid)['status'] ?? null);

        // ...and one with a NEWER version does (the rep owns the enquiry)
        $this->push($headers, ['enquiries' => [$payload(['status' => 'negotiation', 'updated_at' => $this->moment('+1 minute')])]])->assertOk();
        $this->assertSame('negotiation', $e->fresh()->status);
    }

    // ---- the page ------------------------------------------------------------------------------------------------------

    public function test_the_board_renders_with_drag_handles_for_a_manager_who_may_move(): void
    {
        $rep = $this->rep('AB-0001', '9876543210', name: 'Ravi Sharma');
        $this->enquiry($rep, ['client_name' => 'Kota Biotech', 'status' => 'price_sent', 'next_followup_at' => now()->subDays(6)]);
        $this->actingAs($this->manager());

        $html = Livewire::test(ListSalesEnquiries::class)->assertSee('Enquiries')->assertSee('Kota Biotech')->assertSee('Price sent')->assertSee('Move to')->assertSee('6 days late')->html();

        $this->assertStringContainsString('x-sort:handle', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
        $this->assertStringContainsString('Order received', $html);
        $this->assertStringContainsString('Drag a card to another stage', $html);
    }

    public function test_a_view_only_manager_sees_the_board_but_cannot_write(): void
    {
        $rep = $this->rep();
        $e = $this->enquiry($rep, ['client_name' => 'Kota Biotech', 'status' => 'price_sent']);
        $viewer = $this->viewerOf(['sales-enquiries']);
        $this->actingAs($viewer);

        $page = Livewire::test(ListSalesEnquiries::class)->assertSee('Kota Biotech');
        $html = $page->html();
        foreach (['x-sort:handle', 'Move to', 'Drag a card', 'x-sort:item', 'dropEnquiry'] as $writeHint) {
            $this->assertStringNotContainsString($writeHint, $html, "no hint of dragging: $writeHint");
        }

        Livewire::test(ListSalesEnquiries::class)->call('moveEnquiry', $e->uuid, 'negotiation', 'price_sent', $e->client_updated_at->format('Y-m-d H:i:s.v'))->assertForbidden();
        Livewire::test(ListSalesEnquiries::class)->call('dropEnquiry', $e->uuid . '|' . $e->client_updated_at->format('Y-m-d H:i:s.v') . '|price_sent', 'negotiation', 0)->assertForbidden();
        $this->assertSame('price_sent', $e->fresh()->status);
    }

    public function test_nobody_without_the_enquiries_right_and_no_rep_opens_the_screen(): void
    {
        $rep = $this->rep();

        $this->actingAs($this->viewerOf(['sales-meetings']));
        $this->get('/admin/sales-enquiries')->assertForbidden();

        $this->actingAs($rep->user)->get('/admin/sales-enquiries')->assertForbidden();
    }

    public function test_dropping_a_card_moves_it_closing_asks_first_and_the_toast_can_undo(): void
    {
        $rep = $this->rep();
        $e = $this->enquiry($rep, ['status' => 'price_sent']);
        $item = $e->uuid . '|' . $e->client_updated_at->format('Y-m-d H:i:s.v') . '|price_sent';
        $this->actingAs($this->manager());

        $page = Livewire::test(ListSalesEnquiries::class)->call('dropEnquiry', $item, 'negotiation', 0);
        $this->assertSame('negotiation', $e->fresh()->status);

        // a stale card (dragged after the first move) conflicts and writes nothing
        $page->call('dropEnquiry', $item, 'new', 0);
        $this->assertSame('negotiation', $e->fresh()->status);

        // closing asks first, then closes with the note
        $e = $e->fresh();
        $item = $e->uuid . '|' . $e->client_updated_at->format('Y-m-d H:i:s.v') . '|negotiation';
        $page->call('dropEnquiry', $item, 'won', 0)->assertActionMounted('confirmClose');
        $this->assertSame('negotiation', $e->fresh()->status, 'asking is not closing');
        $page->setActionData(['note' => 'PO received'])->callMountedAction();
        $this->assertSame('won', $e->fresh()->status);
        $timeline = $e->fresh()->timeline;
        $this->assertStringContainsString('PO received', end($timeline)['text']);

        // the toast's Undo points at what the move wrote (kept in the session), not at anything the browser sends
        $this->assertNotNull(session('enquiry_undo'));
        $tokens = array_keys(session('enquiry_undo'));
        $page->call('undoMove', end($tokens));
        $this->assertSame('negotiation', $e->fresh()->status);
        $page->call('undoMove', 'forged-token');   // nothing to undo, nothing happens
        $this->assertSame('negotiation', $e->fresh()->status);
    }

    public function test_the_move_to_menu_the_panel_and_its_links_follow_the_permissions(): void
    {
        $rep = $this->rep('AB-0001', '9876543210', name: 'Ravi Sharma');
        $e = $this->enquiry($rep, ['client_name' => 'Kota Biotech', 'next_followup_at' => now()->subDays(6)]);

        $this->actingAs($this->manager());
        $panel = Livewire::test(ListSalesEnquiries::class)->call('openEnquiry', $e->uuid);
        $html = $panel->instance()->getMountedAction()->getModalContent()->render();
        $this->assertStringContainsString('Move to', $html);
        $this->assertStringContainsString('https://wa.me/919876543210?text=', $html);
        $this->assertStringContainsString('Meetings', $html);
        $this->assertStringContainsString('owns this enquiry and its follow-up', $html);

        $this->actingAs($this->viewerOf(['sales-enquiries']));
        $html = Livewire::test(ListSalesEnquiries::class)->call('openEnquiry', $e->uuid)->instance()->getMountedAction()->getModalContent()->render();
        $this->assertStringNotContainsString('Move to', $html);
        $this->assertStringNotContainsString('wa.me', $html, 'the rep\'s phone needs sales-reps');
        $this->assertStringNotContainsString('>Meetings<', $html, 'the meetings link needs sales-meetings');
    }

    public function test_the_list_view_and_the_export_still_work(): void
    {
        $rep = $this->rep();
        $a = $this->enquiry($rep, ['client_name' => 'Open Co', 'status' => 'callback']);
        $b = $this->enquiry($rep, ['client_name' => 'Won Co', 'status' => 'won', 'closed_at' => now(), 'next_followup_at' => null]);
        $this->actingAs($this->manager());

        Livewire::withQueryParams(['view' => 'list'])->test(ListSalesEnquiries::class)
            ->assertCanSeeTableRecords([$a, $b])
            ->filterTable('status', ['won'])->assertCanSeeTableRecords([$b])->assertCanNotSeeTableRecords([$a]);

        // "+N more" opens the List filtered to that stage
        Livewire::test(ListSalesEnquiries::class)->call('showStage', 'callback')->assertSet('viewMode', 'list')->assertCanSeeTableRecords([$a])->assertCanNotSeeTableRecords([$b]);

        // the board's export uses the board's filters
        $this->assertSame([$a->id], SalesInsights::enquiryQuery(['q' => 'open co'])->pluck('id')->all());
        Livewire::test(ListSalesEnquiries::class)->mountAction('exportBoard')->setActionData(['format' => 'csv'])->callMountedAction()->assertHasNoActionErrors();
    }

    public function test_the_board_asks_the_database_the_same_number_of_times_for_50_and_1500_enquiries(): void
    {
        $reps = [$this->rep('AB-0001', '9000000001'), $this->rep('AB-0002', '9000000002')];
        $make = function (int $n) use ($reps) {
            $rows = [];
            for ($i = 0; $i < $n; $i++) {
                $rows[] = [
                    'uuid' => (string) Str::uuid(), 'user_id' => $reps[$i % 2]->user_id, 'client_name' => "Client $i", 'status' => SalesEnquiry::OPEN[$i % 6],
                    'items' => json_encode([['product' => 'Oven', 'quantity' => 1, 'budget_paise' => 100000]]), 'opened_at' => now()->subDays($i % 20)->utc(),
                    'next_followup_at' => $i % 3 ? now()->addDays($i % 7 - 3)->utc() : null, 'client_updated_at' => now()->subDays($i % 9)->format('Y-m-d H:i:s.v'),
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }
            foreach (array_chunk($rows, 300) as $chunk) {
                SalesEnquiry::insert($chunk);
            }
        };
        $this->actingAs($this->manager());
        $count = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            Livewire::test(ListSalesEnquiries::class);
            $n = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $n;
        };

        $make(50);
        $few = $count();
        $make(1450);
        $many = $count();

        $this->assertSame($few, $many, 'no query per enquiry');
    }
}
