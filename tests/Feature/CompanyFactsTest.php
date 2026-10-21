<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\Company;
use App\Support\Seo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyFactsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_years_are_worked_out_from_the_founding_year_1993(): void
    {
        $this->assertSame(1993, Company::foundedYear());
        $this->assertSame(now()->year - 1993, Company::years());
        $this->assertSame(intdiv(now()->year - 1993, 5) * 5, Company::yearsShown());   // 33 years is shown as 30+
        $this->assertStringContainsString(Company::yearsShown() . '+ years', Seo::defaultDescription());
        $this->assertStringContainsString('50+ global brands', Seo::defaultDescription());

        SiteSetting::create(['founded_year' => 2000]);                       // Site Settings wins over the config
        $this->assertSame(2000, Company::foundedYear());
        $this->assertSame(now()->year - 2000, Company::years());
    }

    public function test_who_we_are_shows_the_real_years_and_three_equal_capsules(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('SINCE 1993', $html);
        $this->assertStringNotContainsString('SINCE 1981', $html);
        $this->assertStringContainsString(Company::yearsShown() . '+ years of excellence', $html);   // footer
        $this->assertMatchesRegularExpression('/text-transparent[^>]*>\s*' . Company::yearsShown() . '\s*</', $html);   // the big number

        // the three capsules and their numbers
        foreach (['Customers', 'Industry categories', 'Brands'] as $label) {
            $this->assertStringContainsString('>' . $label . '</h3>', $html);
        }
        $this->assertStringContainsString('All over India', $html);
        $this->assertMatchesRegularExpression('/customers.{1,10}:\s*1000/', $html);
        $this->assertMatchesRegularExpression('/industries.{1,10}:\s*70/', $html);
        $this->assertMatchesRegularExpression('/brands.{1,10}:\s*50/', $html);
        $this->assertSame(3, substr_count($html, 'md:h-[5.5rem]'));

        // the old four-capsule numbers are gone
        $this->assertStringNotContainsString('36500', $html);
        $this->assertStringNotContainsString('>Awards</h3>', $html);
        $this->assertStringNotContainsString('>Branches</h3>', $html);
    }

    public function test_our_story_uses_the_same_facts(): void
    {
        $html = $this->get('/our-story')->assertOk()->getContent();

        $this->assertStringNotContainsString('1981', $html);
        $this->assertStringNotContainsString('36,000', $html);
        $this->assertStringNotContainsString('12+ branches', $html);
        $this->assertStringContainsString('since 1993', $html);
        $this->assertStringContainsString('1000+ customers all over India', $html);
        $this->assertStringContainsString(Company::yearsShown() . '+ Years Strong', $html);
    }

    public function test_our_story_shows_real_numbers_not_template_variables(): void
    {
        $html = $this->get('/our-story')->assertOk()->getContent();

        // a backslash escape left inside a double-quoted PHP string would show on the page
        $this->assertStringNotContainsString("India\\'s", $html);

        $this->assertStringContainsString('Authorised partner of 50+ global brands', $html);   // was shown as {$brandsPlus}
        $this->assertStringContainsString(Company::yearsShown() . '+ years of powering', $html);
        $this->assertStringNotContainsString('Four decades', $html);                           // 1993 is three decades ago
    }

    public function test_no_public_page_shows_a_raw_php_variable(): void
    {
        foreach (['/', '/our-story', '/contact-us', '/verticals', '/insights/blogs', '/insights/news', '/insights/webinars',
                  '/application-resources', '/careers', '/privacy-policy', '/search', '/compare', '/enquiry-list'] as $url) {
            $html = $this->get($url)->getContent();
            // a variable that was put in a single-quoted PHP string shows up as text like {$name} or {{ $name }}
            $this->assertDoesNotMatchRegularExpression('/\{\$[A-Za-z_]+/', strip_tags(preg_replace('/<script.*?<\/script>/s', '', $html)), "raw variable on {$url}");
            $this->assertStringNotContainsString('{{ $', preg_replace('/<script.*?<\/script>/s', '', $html), "raw blade on {$url}");
        }
    }

    public function test_our_story_sections_are_in_the_owners_order(): void
    {
        \App\Models\TeamMember::create(['name' => 'Lead One', 'is_leader' => true, 'is_active' => true]);
        \App\Models\TeamMember::create(['name' => 'Team One', 'is_leader' => false, 'is_active' => true]);

        $html = $this->get('/our-story')->assertOk()->getContent();

        $order = array_map(fn ($needle) => strpos($html, $needle), [
            'Mission', 'Meet our leadership', 'Our Journey', 'Our Values', 'Our people',
        ]);

        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order, 'About/Mission, then Leadership, Journey, Values, People');
    }
}
