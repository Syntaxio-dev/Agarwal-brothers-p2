<?php

namespace Tests\Feature;

use App\Models\Insight;
use App\Rules\SafeLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsLoadTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_load_adds_the_sourced_articles_as_drafts_and_is_safe_to_repeat(): void
    {
        $this->artisan('news:load')->assertSuccessful();

        $news = Insight::where('type', 'news')->get();
        $this->assertGreaterThanOrEqual(12, $news->count());
        $this->assertSame(0, $news->where('is_active', true)->count());

        foreach ($news as $a) {
            $types = array_column($a->blocks, 'type');
            $this->assertSame(['text', 'sources'], $types, $a->slug);
            foreach ($a->blocks[1]['data']['items'] as $s) {
                $this->assertTrue(SafeLink::passes($s['url']), $a->slug . ' ' . $s['url']);
            }
            $this->assertNotNull($a->event_date, $a->slug);
        }

        $count = Insight::count();
        $this->artisan('news:load')->assertSuccessful();
        $this->assertSame($count, Insight::count());
    }

    public function test_a_loaded_article_shows_its_text_and_sources_once_published(): void
    {
        $this->artisan('news:load', ['--publish' => true])->assertSuccessful();
        $a = Insight::where('type', 'news')->firstOrFail();

        $this->get(route('insights.show', $a->slug))->assertOk()->assertSee('Sources');
        $this->get(route('insights.news'))->assertOk()->assertSee($a->title);
    }
}
