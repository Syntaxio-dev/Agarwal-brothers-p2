<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesExpense;

class SyncPullTest extends SalesTestCase
{
    private function pull(array $headers, ?string $since = null)
    {
        return $this->getJson('/api/sales/v1/sync/pull' . ($since ? '?since=' . urlencode($since) : ''), $headers);
    }

    public function test_a_new_phone_gets_everything_back(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->expense(['note' => 'Taxi', 'has_bill' => true]);
        $c = $this->client();
        $m = $this->meeting(['client_uuid' => $c['uuid']]);
        $this->push($headers, ['expenses' => [$e], 'clients' => [$c], 'meetings' => [$m]])->assertOk();

        $all = $this->pull($headers)->assertOk();

        $this->assertSame($e['uuid'], $all->json('expenses.0.uuid'));
        $this->assertSame(45000, $all->json('expenses.0.amount_paise'));
        $this->assertSame('Taxi', $all->json('expenses.0.note'));
        $this->assertSame('pending', $all->json('expenses.0.status'));
        $this->assertFalse($all->json('expenses.0.has_bill'), 'the photo has not been uploaded yet');
        $this->assertSame($c['uuid'], $all->json('clients.0.uuid'));
        $this->assertSame($c['uuid'], $all->json('meetings.0.client_uuid'));
        $this->assertFalse($all->json('has_more'));
        $this->assertNotEmpty($all->json('cursor'));
    }

    public function test_the_office_decision_comes_back_to_the_phone(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->expense();
        $this->push($headers, ['expenses' => [$e]])->assertOk();
        $first = $this->pull($headers)->assertOk();

        $this->travel(5)->seconds();
        SalesExpense::first()->reject($this->manager(), 'Bill is missing');

        $next = $this->pull($headers, $first->json('cursor'))->assertOk();

        $this->assertSame('rejected', $next->json('expenses.0.status'));
        $this->assertSame('Bill is missing', $next->json('expenses.0.reject_reason'));
        $this->assertNotNull($next->json('expenses.0.reviewed_at'));
    }

    public function test_only_what_changed_since_the_cursor_comes_back(): void
    {
        $headers = $this->headers($this->rep());
        $old = $this->expense();
        $this->push($headers, ['expenses' => [$old]])->assertOk();

        // the cursor overlaps by one second on purpose (merging is idempotent), so wait before asking
        $this->travel(5)->seconds();
        $cursor = $this->pull($headers)->json('cursor');

        $this->travel(10)->seconds();
        $new = $this->expense();
        $this->push($headers, ['expenses' => [$new]])->assertOk();

        $uuids = collect($this->pull($headers, $cursor)->json('expenses'))->pluck('uuid');
        $this->assertTrue($uuids->contains($new['uuid']));
        $this->assertFalse($uuids->contains($old['uuid']));
    }

    public function test_a_rep_never_sees_another_reps_records(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $theirs = $this->expense(['note' => 'private to A']);
        $this->push($this->headers($a), ['expenses' => [$theirs], 'clients' => [$this->client()], 'meetings' => [$this->meeting()]])->assertOk();

        $answer = $this->pull($this->headers($b))->assertOk();

        $this->assertSame([], $answer->json('expenses'));
        $this->assertSame([], $answer->json('clients'));
        $this->assertSame([], $answer->json('meetings'));
        $this->assertStringNotContainsString('private to A', $answer->getContent());
    }

    public function test_deleted_records_are_flagged_so_a_phone_can_drop_them(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->expense();
        $this->push($headers, ['expenses' => [$e]])->assertOk();
        $this->push($headers, ['deleted' => ['expenses' => [$e['uuid']]]])->assertOk();

        $this->assertTrue($this->pull($headers)->json('expenses.0.deleted'));
    }

    public function test_a_large_backlog_comes_in_pages_without_losing_anything(): void
    {
        config(['sales.max_pull_records' => 2]);
        $headers = $this->headers($this->rep());

        $uuids = [];
        foreach (range(1, 5) as $i) {
            $e = $this->expense(['note' => "n$i"]);
            $uuids[] = $e['uuid'];
            $this->push($headers, ['expenses' => [$e]])->assertOk();
            $this->travel(2)->seconds();   // different seconds, so the paging cursor can move forward
        }

        $seen = [];
        $since = null;
        for ($round = 0; $round < 10; $round++) {
            $page = $this->pull($headers, $since)->assertOk();
            foreach ($page->json('expenses') as $row) {
                $seen[$row['uuid']] = true;
            }
            if (! $page->json('has_more')) {
                break;
            }
            $since = $page->json('cursor');
        }

        $this->assertEqualsCanonicalizing($uuids, array_keys($seen));
    }

    public function test_pull_needs_a_signed_in_active_rep(): void
    {
        $this->getJson('/api/sales/v1/sync/pull')->assertStatus(401);

        $profile = $this->rep();
        $headers = $this->headers($profile);
        $profile->update(['is_active' => false]);

        $this->pull($headers)->assertStatus(403)->assertJsonPath('code', 'ACCOUNT_INACTIVE');
    }
}
