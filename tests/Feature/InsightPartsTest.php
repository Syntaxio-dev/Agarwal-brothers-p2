<?php

namespace Tests\Feature;

use App\Filament\Resources\Insights\Pages\CreateInsight;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InsightPartsTest extends TestCase
{
    use RefreshDatabase;

    private function article(string $type, array $blocks, array $over = []): Insight
    {
        return Insight::create(array_merge([
            'type' => $type, 'title' => 'Parts ' . uniqid(), 'slug' => 'parts-' . uniqid(), 'excerpt' => 'Short', 'is_active' => true,
            'event_date' => '2026-10-01', 'blocks' => $blocks,
        ], $over));
    }

    private function parts(): array
    {
        return [
            ['type' => 'text', 'data' => ['content' => '<p>First paragraph.</p>']],
            ['type' => 'image', 'data' => ['image' => 'insights/body/one.webp', 'alt' => 'A balance', 'caption' => 'Figure one', 'size' => 'medium']],
            ['type' => 'text', 'data' => ['content' => '<p>Second paragraph.</p>']],
            ['type' => 'gallery', 'data' => ['images' => ['insights/body/a.webp', 'insights/body/b.webp', 'insights/body/c.webp'], 'style' => 'carousel', 'caption' => 'Our lab']],
            ['type' => 'gallery', 'data' => ['images' => ['insights/body/d.webp', 'insights/body/e.webp'], 'style' => 'grid']],
            ['type' => 'sources', 'data' => ['items' => [
                ['label' => 'Maker guide', 'url' => 'https://example.com/guide', 'accessed' => '2026-10-20'],
                ['label' => 'Bad link', 'url' => 'javascript:alert(1)'],
            ]]],
        ];
    }

    public function test_parts_show_in_the_order_they_were_added_for_blogs_and_news(): void
    {
        foreach (['blog', 'news'] as $type) {
            $a = $this->article($type, $this->parts());
            $html = $this->get(route('insights.show', $a->slug))->assertOk()->getContent();

            $order = array_map(fn ($needle) => strpos($html, $needle), ['First paragraph.', 'storage/insights/body/one.webp', 'Second paragraph.', 'storage/insights/body/a.webp', 'storage/insights/body/d.webp', 'Maker guide']);
            $this->assertNotContains(false, $order, $type);
            $sorted = $order;
            sort($sorted);
            $this->assertSame($sorted, $order, "$type: parts are in the saved order");

            $this->assertStringContainsString('Figure one', $html);
            $this->assertStringContainsString('alt="A balance"', $html);
            $this->assertStringContainsString('max-w-2xl', $html);                           // medium picture
        }
    }

    public function test_a_slider_has_arrows_dots_counter_swipe_and_keys_a_grid_has_none(): void
    {
        $html = $this->get(route('insights.show', $this->article('blog', $this->parts())->slug))->getContent();

        $this->assertSame(1, substr_count($html, 'aria-roledescription="carousel"'));        // only the 3-picture slider, not the grid
        $this->assertStringContainsString('aria-label="Previous photo"', $html);
        $this->assertStringContainsString('aria-label="Next photo"', $html);
        $this->assertSame(3, substr_count($html, 'aria-label="Show photo '));
        $this->assertStringContainsString('/ 3', $html);
        $this->assertStringContainsString('@touchend.passive', $html);
        $this->assertStringContainsString('touch-action: pan-y pinch-zoom', $html);
        $this->assertStringContainsString('@keydown.arrow-right.prevent', $html);
    }

    public function test_sources_are_listed_as_safe_links_and_unsafe_ones_are_dropped(): void
    {
        $html = $this->get(route('insights.show', $this->article('blog', $this->parts())->slug))->getContent();

        $this->assertStringContainsString('href="https://example.com/guide"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $html);
        $this->assertStringContainsString('read 2026-10-20', $html);
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringNotContainsString('Bad link', $html);
    }

    public function test_the_plain_text_is_kept_in_content_for_cards_search_and_read_time(): void
    {
        $a = $this->article('blog', $this->parts());

        $this->assertStringContainsString('First paragraph.', $a->fresh()->content);
        $this->assertStringContainsString('Second paragraph.', $a->fresh()->content);
        $this->assertStringNotContainsString('storage', $a->fresh()->content);
    }

    public function test_an_old_article_with_only_text_still_shows_its_text(): void
    {
        $a = Insight::create(['type' => 'blog', 'title' => 'Old', 'slug' => 'old-one', 'content' => '<p>Old body text.</p>', 'is_active' => true, 'event_date' => '2026-10-01']);

        $this->assertNull($a->blocks);
        $this->assertSame('text', $a->articleParts()[0]['type']);
        $this->get(route('insights.show', $a->slug))->assertOk()->assertSee('Old body text.', false);
    }

    public function test_pdf_only_article_without_parts_shows_no_empty_body(): void
    {
        $a = Insight::create(['type' => 'news', 'title' => 'Poster', 'slug' => 'poster-only', 'is_active' => true, 'event_date' => '2026-10-01']);

        $this->assertSame([], $a->articleParts());
        $this->get(route('insights.show', $a->slug))->assertOk();
    }

    public function test_the_admin_can_save_an_article_built_from_parts(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(CreateInsight::class)
            ->fillForm([
                'type' => 'blog', 'title' => 'Builder test', 'slug' => 'builder-test', 'is_active' => true,
                'blocks' => [
                    ['type' => 'text', 'data' => ['content' => '<p>Hello parts</p>']],
                    ['type' => 'sources', 'data' => ['items' => [['label' => 'Guide', 'url' => 'https://example.com/g']]]],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $saved = Insight::where('slug', 'builder-test')->firstOrFail();
        $this->assertSame(['text', 'sources'], array_column($saved->blocks, 'type'));
        $this->assertStringContainsString('Hello parts', $saved->content);
    }

    public function test_an_unsafe_source_link_is_refused_in_the_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(CreateInsight::class)
            ->fillForm([
                'type' => 'blog', 'title' => 'Bad link', 'slug' => 'bad-link', 'is_active' => true,
                'blocks' => [['type' => 'sources', 'data' => ['items' => [['label' => 'X', 'url' => 'javascript:alert(1)']]]]],
            ])
            ->call('create')
            ->assertHasFormErrors();

        $this->assertDatabaseMissing('insights', ['slug' => 'bad-link']);
    }
}
