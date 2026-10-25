<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Resources\SalesProducts\Pages\EditSalesProduct;
use App\Sales\Models\SalesProduct;
use App\Sales\Models\SalesScript;
use Illuminate\Support\Str;
use Livewire\Livewire;

/** The "before the meeting" side of the product guide (client types, pitch, notes) and the reps' own scripts. */
class GuideBriefTest extends SalesTestCase
{
    private function product(array $over = []): SalesProduct
    {
        return SalesProduct::create(array_merge([
            'name' => 'Lab Weighing Balance', 'group' => 'Balances and weighing', 'sort' => 34,
            'premium_brands' => ['Sartorius'], 'economy_brands' => ['Aczet'], 'lite_brands' => [],
        ], $over));
    }

    private function script(string $productUuid, array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'product_uuid' => $productUuid,
            'product_name' => 'Lab Weighing Balance',
            'title' => 'For pharma buyers',
            'body' => 'Good morning. Our balances weigh to 0.1 mg.',
            'updated_at' => $this->moment(),
        ], $over);
    }

    // ---- the brief fields on the phone's guide ----------------------------------------------------

    public function test_the_guide_carries_the_brief_fields_and_empty_ones_are_empty(): void
    {
        $headers = $this->headers($this->rep());
        $this->product([
            'client_types' => ['pharma_qc', 'made_up', 'food_dairy'],
            'pitch' => 'Weighs to **0.1 mg**.',
            'key_notes' => ['Draft shield matters'],
            'formula' => 'a = b',
            'do_list' => ['Ask the capacity'],
            'avoid_list' => ['Quote blind'],
            'match_rules' => [['if' => 'Audit trail', 'then' => 'Premium']],
        ]);
        $this->product(['name' => 'Vortex Mixer', 'sort' => 24]);

        $list = $this->getJson('/api/sales/v1/products', $headers)->assertOk()->json('products');
        $vortex = collect($list)->firstWhere('name', 'Vortex Mixer');
        $balance = collect($list)->firstWhere('name', 'Lab Weighing Balance');

        $this->assertSame(['pharma_qc', 'food_dairy'], $balance['client_types'], 'an unknown kind of client never reaches a phone');
        $this->assertSame('Weighs to **0.1 mg**.', $balance['pitch']);
        $this->assertSame(['Draft shield matters'], $balance['key_notes']);
        $this->assertSame(['Ask the capacity'], $balance['do']);
        $this->assertSame(['Quote blind'], $balance['avoid']);
        $this->assertSame([['if' => 'Audit trail', 'then' => 'Premium']], $balance['match_rules']);
        $this->assertSame([[], '', [], [], [], []], [$vortex['client_types'], $vortex['pitch'], $vortex['key_notes'], $vortex['do'], $vortex['avoid'], $vortex['match_rules']]);
    }

    public function test_editing_the_brief_changes_the_version_so_phones_fetch_it_again(): void
    {
        $headers = $this->headers($this->rep());
        $p = $this->product();
        $first = $this->getJson('/api/sales/v1/products', $headers)->json('version');

        $this->travel(2)->seconds();
        $p->update(['pitch' => 'A new pitch']);

        $this->getJson('/api/sales/v1/products?version=' . $first, $headers)->assertOk()->assertJsonPath('unchanged', false);
    }

    public function test_the_manager_writes_the_brief_in_the_admin(): void
    {
        $this->actingAs($this->manager());
        $p = $this->product();

        Livewire::test(EditSalesProduct::class, ['record' => $p->getKey()])
            ->fillForm([
                'client_types' => ['pharma_qc'],
                'pitch' => 'Weighs to **0.1 mg**.',
                'key_notes' => [['item' => 'One'], ['item' => 'Two']],
                'do_list' => [['item' => 'Ask first']],
                'match_rules' => [['if' => 'Needs audit', 'then' => 'Premium']],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $p->fresh();
        $this->assertSame(['pharma_qc'], $fresh->client_types);
        $this->assertSame('Weighs to **0.1 mg**.', $fresh->pitch);
        $this->assertCount(2, $fresh->key_notes);

        // limits: at most 5 notes, 3 do lines, 4 answers, a pitch of 400 letters
        Livewire::test(EditSalesProduct::class, ['record' => $p->getKey()])
            ->fillForm(['key_notes' => array_map(fn ($n) => ['item' => (string) $n], [1, 2, 3, 4, 5, 6]), 'pitch' => str_repeat('x', 401)])
            ->call('save')
            ->assertHasFormErrors(['pitch', 'key_notes']);
    }

    // ---- scripts ------------------------------------------------------------------------------------

    public function test_a_script_is_stored_for_the_rep_and_comes_back_on_a_pull(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $p = $this->product();
        $script = $this->script($p->uuid);

        $this->assertSame('accepted', $this->answer($this->push($a, ['scripts' => [$script]])->assertOk(), 'scripts', $script['uuid'])['status']);
        $this->assertSame('Good morning. Our balances weigh to 0.1 mg.', SalesScript::first()->body);

        $pull = $this->getJson('/api/sales/v1/sync/pull', $a)->assertOk();
        $this->assertSame($script['uuid'], $pull->json('scripts.0.uuid'));
        $this->assertSame('For pharma buyers', $pull->json('scripts.0.title'));
        $this->assertFalse($pull->json('scripts.0.deleted'));

        // the same again is a duplicate, a newer one changes it
        $this->assertSame('duplicate', $this->answer($this->push($a, ['scripts' => [$script]])->assertOk(), 'scripts', $script['uuid'])['status']);
        $this->travel(2)->seconds();
        $newer = $script + [];
        $newer['body'] = 'A better pitch for balances.';
        $newer['updated_at'] = $this->moment();
        $this->assertSame('accepted', $this->answer($this->push($a, ['scripts' => [$newer]])->assertOk(), 'scripts', $script['uuid'])['status']);
        $this->assertSame('A better pitch for balances.', SalesScript::first()->body);
    }

    public function test_another_rep_never_sees_or_touches_a_script(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $script = $this->script($this->product()->uuid);
        $this->push($a, ['scripts' => [$script]])->assertOk();

        $this->assertSame([], $this->getJson('/api/sales/v1/sync/pull', $b)->json('scripts'));

        $taken = $this->push($b, ['scripts' => [$script + ['body' => 'Stolen words here.']]])->assertOk();
        $this->assertSame('UUID_TAKEN', $this->answer($taken, 'scripts', $script['uuid'])['code']);
        $this->assertSame('Good morning. Our balances weigh to 0.1 mg.', SalesScript::first()->body);

        // deleting somebody else's script changes nothing (and says nothing)
        $this->push($b, ['deleted' => ['scripts' => [$script['uuid']]]])->assertOk();
        $this->assertSame(1, SalesScript::count());
    }

    public function test_script_rules_and_delete(): void
    {
        $a = $this->headers($this->rep());
        $p = $this->product();

        $bad = $this->push($a, ['scripts' => [
            $this->script($p->uuid, ['body' => 'ab']),
            $this->script($p->uuid, ['product_name' => 'x']),
            $this->script($p->uuid, ['body' => str_repeat('x', 3001)]),
            $this->script($p->uuid, ['product_uuid' => 'not-a-uuid']),
            $this->script($p->uuid, ['updated_at' => $this->moment('+2 hours')]),
        ]])->assertOk();
        foreach (range(0, 4) as $i) {
            $this->assertSame('rejected', $bad->json("results.scripts.$i.status"));
        }
        $this->assertSame(0, SalesScript::count());

        $good = $this->script($p->uuid);
        $this->push($a, ['scripts' => [$good]])->assertOk();
        $done = $this->push($a, ['deleted' => ['scripts' => [$good['uuid']]]])->assertOk();
        $this->assertSame('accepted', $done->json('results.deleted.scripts.0.status'));
        $this->assertSame(0, SalesScript::count());

        $this->travel(2)->seconds();
        $this->assertTrue($this->getJson('/api/sales/v1/sync/pull', $a)->json('scripts.0.deleted'));
    }

    public function test_managers_read_the_scripts_in_the_admin_and_reps_never_do(): void
    {
        $rep = $this->rep('REP001', '9000000001');
        $script = $this->script($this->product()->uuid);
        $this->push($this->headers($rep), ['scripts' => [$script]])->assertOk();

        $manager = $this->manager();
        $row = SalesScript::first();
        $this->assertTrue($manager->can('view', $row));
        $this->assertFalse($manager->can('update', $row));
        $this->assertTrue($manager->can('delete', $row));
        $other = $this->rep('REP002', '9000000002');
        $this->assertFalse($other->user->can('view', $row), 'another rep cannot read it');
        $this->assertFalse($other->user->can('delete', $row));

        $this->actingAs($manager)->get('/admin/sales-scripts')->assertOk()->assertSee('Lab Weighing Balance');
    }

    public function test_the_management_page_counts_the_scripts(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $this->push($a, ['scripts' => [$this->script($this->product()->uuid)]])->assertOk();

        $this->actingAs($this->manager())->get('/admin/sales-management')->assertOk()->assertSee('1 own scripts from 1 reps');
    }
}
