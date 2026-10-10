<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrollRevealTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_sections_below_the_first_screen_are_marked_to_reveal(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(7, substr_count($html, 'data-reveal'));          // at least the section headings
        // the hero carousel (first section) is never animated, so the first screen shows at once
        $firstSection = strstr(strstr($html, '<section'), '</section>', true);
        $this->assertStringNotContainsString('data-reveal', $firstSection);
    }

    public function test_the_page_has_a_failsafe_so_nothing_stays_hidden(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('reveal-failsafe', $html);
        $this->assertStringContainsString("classList.add('js')", $html);
    }

    public function test_the_reveal_styles_respect_reduced_motion_and_only_animate_opacity_and_position(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('@media (prefers-reduced-motion: no-preference)', $css);
        $this->assertMatchesRegularExpression('/\.js:not\(\.reveal-failsafe\) \[data-reveal\]:not\(\.is-revealed\)\s*\{\s*opacity:\s*0;/', $css);
        // each entrance animation starts from a faded, slightly moved position and ends at the element's own style
        foreach (['up', 'left', 'right', 'zoom', 'fade'] as $kind) {
            $this->assertStringContainsString("@keyframes reveal-{$kind}", $css);
        }
        $this->assertStringContainsString('600ms', $css);                                   // balanced speed
    }

    public function test_tall_blocks_are_split_up_and_get_a_bigger_entrance(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $js = file_get_contents(resource_path('js/reveal.js'));

        $this->assertStringContainsString('@keyframes reveal-up-lg', $css);
        $this->assertStringContainsString('reveal-lg', $js);
        // an item starts once 200px (or 60% of a small item) is on screen, not at the first visible pixel
        $this->assertStringContainsString('MIN_VISIBLE_PX = 200', $js);
        $this->assertStringContainsString('startedAbove', $js);                           // jumping down never leaves a half-hidden block

        // blogs and news are not one big block any more: rows, cover, cards and the links under them each reveal
        $template = file_get_contents(resource_path('views/public/home.blade.php'));
        $this->assertGreaterThanOrEqual(25, substr_count($template, 'data-reveal'));
        $this->assertStringContainsString('<button data-reveal="left"', $template);    // blog rows
        $this->assertStringContainsString('<div data-reveal="right" class="order-1 lg:order-2', $template);   // blog cover
    }

    public function test_each_brand_row_stops_on_its_own_when_hovered(): void
    {
        $html = $this->get('/')->getContent();
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('.brand-marquee-track:hover', $css);
        $this->assertStringContainsString('.brand-marquee-track:focus-within', $css);
        // the old "stop everything" switch on the shared container is gone
        $this->assertStringNotContainsString("'is-paused': paused", $html);
        $this->assertDoesNotMatchRegularExpression('/data-reveal="fade" x-data="\{ paused/', $html);
    }

    public function test_our_story_reveals_its_sections_and_draws_the_journey_timeline(): void
    {
        $html = $this->get('/our-story')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(25, substr_count($html, 'data-reveal'));
        $this->assertStringContainsString('data-reveal="draw"', $html);
        $this->assertSame(4, substr_count($html, 'class="tl-node'));       // one pop-in dot per year
        $this->assertStringContainsString('tl-line', $html);
        $this->assertStringContainsString('story-hero-title', $html);      // banner intro hooks
        // tabs: a sliding pill sits behind the active chip and the panels slide a little as they fade
        $this->assertStringContainsString('x-ref="mission"', $html);
        $this->assertStringContainsString('translate-y-2', $html);
        // the banner itself is never hidden by scroll reveal (first screen shows at once)
        $banner = strstr(strstr($html, 'story-hero-img'), 'story-hero-crumb', true);
        $this->assertStringNotContainsString('data-reveal', $banner);

        $css = file_get_contents(resource_path('css/app.css'));
        foreach (['tl-wipe', 'tl-pop', 'tl-stem', 'icon-pop', 'hero-zoom', 'hero-rise'] as $keyframes) {
            $this->assertStringContainsString("@keyframes {$keyframes}", $css);
        }
        // all of it lives in the "motion allowed" block, so reduced-motion visitors see the plain page
        $motion = substr($css, strpos($css, '@media (prefers-reduced-motion: no-preference) {'));
        $this->assertStringContainsString('.story-hero-title', $motion);
        $this->assertStringContainsString('[data-reveal="draw"].is-revealed .tl-line', $motion);
    }

    public function test_careers_pages_reveal_use_the_shared_breadcrumb_and_have_the_new_touches(): void
    {
        $opening = \App\Models\JobOpening::create([
            'title' => 'Sales Executive', 'slug' => 'sales-executive', 'location' => 'Jaipur',
            'employment_type' => 'full-time', 'is_active' => true,
        ]);

        $list = $this->get(route('careers'))->assertOk()->getContent();
        $this->assertGreaterThanOrEqual(12, substr_count($list, 'data-reveal'));
        $this->assertStringContainsString('class="crumbs"', $list);              // same breadcrumb as every other page
        $this->assertStringContainsString('edge-left', $list);                   // role cards: cyan edge on hover
        $this->assertStringContainsString('arrow-nudge-down', $list);
        $this->assertStringContainsString('href="#apply"', $list);               // banner jumps to the form
        $this->assertStringContainsString('BreadcrumbList', $list);

        $detail = $this->get(route('careers.show', $opening))->assertOk()->getContent();
        $this->assertStringContainsString('class="crumbs"', $detail);
        $this->assertStringContainsString('data-reveal="left"', $detail);
        $this->assertStringContainsString('data-reveal="right"', $detail);

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('.edge-left::before', $css);
        $this->assertStringContainsString('.arrow-nudge-down', $css);
    }

    public function test_contact_page_reveals_drops_pins_and_uses_the_shared_breadcrumb(): void
    {
        $html = $this->get('/contact-us')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(14, substr_count($html, 'data-reveal'));
        $this->assertStringContainsString('class="crumbs"', $html);
        $this->assertSame(2, substr_count($html, 'class="map-pin'));              // head office + Jodhpur drop onto the map
        $this->assertStringContainsString('href="#contact-form"', $html);         // header button jumps to the form
        $this->assertStringContainsString('edge-top', $html);
        $this->assertStringContainsString('pop-icon', $html);

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('@keyframes pin-drop', $css);
        $this->assertStringContainsString('.edge-top::before', $css);
    }

    public function test_application_resources_page_reveals_and_cards_react_on_hover(): void
    {
        \App\Models\ApplicationResource::create(['title' => 'HPLC guide', 'category' => 'guide', 'is_active' => true]);

        $html = $this->get('/application-resources')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(6, substr_count($html, 'data-reveal'));
        $this->assertStringContainsString('class="crumbs"', $html);
        $this->assertStringContainsString('bar-grow', $html);                      // coloured top bar grows as the card arrives
        $this->assertStringContainsString('group-hover:bg-link', $html);           // button follows the card hover
        $this->assertStringContainsString('arrow-nudge', $html);
        $this->assertStringContainsString('pop-icon', $html);

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('@keyframes bar-grow', $css);
        $this->assertStringContainsString('.group:hover .btn-primary .arrow-nudge', $css);
    }

    public function test_blogs_and_news_pages_reveal_and_blog_cards_react_on_hover(): void
    {
        \App\Models\Insight::create(['type' => 'blog', 'title' => 'HPLC basics', 'slug' => 'hplc-basics', 'is_active' => true]);
        \App\Models\Insight::create(['type' => 'news', 'title' => 'Analytica 2026', 'slug' => 'analytica-2026', 'is_active' => true]);

        foreach (['/insights/blogs', '/insights/news-events'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertGreaterThanOrEqual(3, substr_count($html, 'data-reveal'), $url);
            $this->assertStringContainsString('class="crumbs"', $html);               // shared breadcrumb
            $this->assertStringContainsString('edge-top', $html);                     // cyan edge on card hover
            $this->assertStringContainsString('bar-grow', $html);                     // underline under the heading grows
            $this->assertStringContainsString('group-hover:bg-link', $html);          // button follows the card
        }

        // the picture banner of the blogs page is never hidden by scroll reveal: it gets a calm intro instead
        $blogs = $this->get('/insights/blogs')->getContent();
        $this->assertStringContainsString('intro-rise', $blogs);
        $this->assertStringNotContainsString('data-reveal class="intro', $blogs);

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('.intro-rise', $css);
        $this->assertStringContainsString('.intro-zoom', $css);
    }

    public function test_webinars_page_reveals_pops_the_countdown_and_rows_react_on_hover(): void
    {
        \App\Models\Insight::create([
            'type' => 'webinar', 'title' => 'Future of HPLC', 'slug' => 'future-of-hplc', 'is_active' => true,
            'starts_at' => now('Asia/Kolkata')->addDays(5), 'event_date' => now('Asia/Kolkata')->addDays(5)->toDateString(),
        ]);
        \App\Models\Insight::create([
            'type' => 'webinar', 'title' => 'Old session', 'slug' => 'old-session', 'is_active' => true,
            'starts_at' => now('Asia/Kolkata')->subDays(20), 'event_date' => now('Asia/Kolkata')->subDays(20)->toDateString(),
        ]);

        $html = $this->get('/insights/webinars')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(8, substr_count($html, 'data-reveal'));
        $this->assertStringContainsString('class="crumbs"', $html);
        $this->assertStringContainsString('edge-left', $html);                    // rows: cyan left edge on hover
        $this->assertStringContainsString('group-hover:bg-link', $html);          // button follows the row
        $this->assertSame(4, substr_count($html, 'class="pop-tile'));             // days / hours / minutes / seconds pop in
        $this->assertStringContainsString('arrow-nudge', $html);

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('.pop-tile', $css);
    }

    public function test_search_page_reveals_and_cards_react_on_hover(): void
    {
        $html = $this->get('/search')->assertOk()->getContent();
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'data-reveal'));
        $this->assertStringContainsString('class="crumbs"', $html);
        // the live-search bar is part of the reveal, but its dropdown must stay usable
        $this->assertStringContainsString('data-reveal action="' . route('search') . '"', $html);

        $none = $this->get('/search?q=zzzqqq')->assertOk()->getContent();
        $this->assertStringContainsString('pop-icon', $none);                       // "no match" icon pops in
    }

    public function test_enquiry_list_uses_plain_entrance_for_alpine_rows_never_data_reveal(): void
    {
        $html = $this->get('/enquiry-list')->assertOk()->getContent();

        // rows are built by Alpine after the page loads: reveal.js never sees them, so they must not wait for it
        $this->assertStringContainsString('row-in', $html);
        $row = strstr(strstr($html, 'row-in'), 'x-text="n + 1"', true);
        $this->assertStringNotContainsString('data-reveal', $row);
        $this->assertStringContainsString('hover:text-alert', $html);               // Remove turns alert-orange on hover
        $this->assertStringContainsString('data-reveal="right" id="enquiry-details"', $html);

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('.row-in', $css);
    }

    public function test_enquiry_sent_confirmation_pops_in(): void
    {
        $html = $this->withSession(['enquiry_sent' => 2])->get('/enquiry-list')->assertOk()->getContent();

        $this->assertStringContainsString('Enquiry sent', $html);
        $this->assertStringContainsString('data-reveal="zoom"', $html);
        $this->assertStringContainsString('pop-icon', $html);
    }

    public function test_article_compare_and_privacy_pages_reveal_and_react_on_hover(): void
    {
        $blog = \App\Models\Insight::create(['type' => 'blog', 'title' => 'HPLC basics', 'slug' => 'hplc-basics', 'is_active' => true, 'content' => '<p>Hello</p>']);
        \App\Models\Insight::create(['type' => 'blog', 'title' => 'Another post', 'slug' => 'another-post', 'is_active' => true]);

        $article = $this->get(route('insights.show', $blog->slug))->assertOk()->getContent();
        $this->assertGreaterThanOrEqual(6, substr_count($article, 'data-reveal'));
        $this->assertStringContainsString('edge-top', $article);                      // related cards
        $this->assertStringContainsString('hover:-translate-y-0.5', $article);       // share buttons lift

        $compare = $this->get('/compare')->assertOk()->getContent();
        $this->assertGreaterThanOrEqual(4, substr_count($compare, 'data-reveal'));

        $privacy = $this->get('/privacy-policy')->assertOk()->getContent();
        $this->assertGreaterThanOrEqual(8, substr_count($privacy, 'data-reveal'));
        $this->assertStringContainsString('hover:bg-ice/70', $privacy);               // table rows tint on hover
    }

    public function test_error_pages_use_only_css_intro_animation_never_the_reveal_script(): void
    {
        // the error layout is stand-alone (it must work when the database or scripts fail), so no data-reveal there
        foreach (['403', '404', '419', '429', '500', '503'] as $code) {
            $html = view('errors.' . $code)->render();
            $this->assertStringContainsString('intro-rise', $html, $code);
            $this->assertStringNotContainsString('data-reveal', $html, $code);
        }

        $this->get('/definitely-not-a-page')->assertNotFound()->assertSee('intro-rise', false);
    }

    public function test_footer_and_sidebar_get_small_touches_on_every_page(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(6, substr_count($html, 'nav-link block px-3'));              // six plain sidebar links
        $this->assertStringContainsString('nav-link flex items-center', $html);         // the Insights button
        $footer = strstr($html, '<footer');
        $this->assertSame(5, substr_count($footer, 'data-reveal'));                     // four footer columns + the sales strip
        $this->assertStringContainsString('rounded-[2rem]', $footer);                    // a rounded floating card, not a full-width block
        $this->assertStringContainsString('hover:translate-x-1', $footer);

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('.nav-link::before', $css);
    }

    public function test_page_to_page_fade_is_wired_up_and_leaves_special_links_alone(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('page-fade relative flex flex-col min-h-screen', $html);   // content fades, sidebar stays

        $js = file_get_contents(resource_path('js/page-fade.js'));
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $js);
        $this->assertStringContainsString('ctrlKey', $js);
        $this->assertStringContainsString("a.hasAttribute('download')", $js);
        $this->assertStringContainsString('admin|livewire|storage', $js);
        $this->assertStringContainsString("import './page-fade'", file_get_contents(resource_path('js/app.js')));

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('html.page-leaving .page-fade', $css);
        $this->assertMatchesRegularExpression('/@media \(prefers-reduced-motion: no-preference\) \{\s*\.page-fade/', $css);
    }
}
