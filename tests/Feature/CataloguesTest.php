<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CataloguesTest extends TestCase
{
    use RefreshDatabase;

    private function two(): array
    {
        return [
            ['title' => 'Equipment catalogue', 'file' => 'catalogue/equipment.pdf'],
            ['title' => 'Consumables catalogue', 'file' => 'catalogue/consumables.pdf'],
        ];
    }

    public function test_the_list_skips_rows_without_a_file_and_names_a_row_without_a_title(): void
    {
        $s = SiteSetting::create(['catalogues' => [['title' => 'A', 'file' => 'catalogue/a.pdf'], ['title' => 'No file', 'file' => null], ['title' => ' ', 'file' => 'catalogue/b.pdf']]]);

        $list = $s->catalogueList();

        $this->assertSame(['A', 'Catalogue'], array_column($list, 'title'));
        $this->assertStringEndsWith('storage/catalogue/a.pdf', $list[0]['url']);
        $this->assertSame([], (new SiteSetting)->catalogueList());
    }

    public function test_no_catalogue_means_no_button_anywhere(): void
    {
        SiteSetting::create([]);

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('nav-cat', $home);
        $this->assertStringNotContainsString('catalogue-menu', $home);
        $this->assertStringNotContainsString('Download product catalogue', $home);
    }

    public function test_one_catalogue_keeps_the_direct_download_button(): void
    {
        SiteSetting::create(['catalogues' => [['title' => 'Product catalogue', 'file' => 'catalogue/only.pdf']]]);

        $home = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('class="nav-cat btn-primary', $home);
        $this->assertStringContainsString('storage/catalogue/only.pdf', $home);
        $this->assertStringNotContainsString('catalogue-menu', $home);
    }

    public function test_two_catalogues_give_a_dropdown_in_the_top_bar_and_two_links_in_the_phone_menu(): void
    {
        SiteSetting::create(['catalogues' => $this->two()]);

        $home = $this->get('/')->assertOk()->getContent();

        // top bar: one button that opens a list with both catalogues, each a PDF download
        // the button uses the same Catalogue -> Download word swap as the single button, and keeps it while the list is open
        $this->assertStringContainsString('<span class="cat-a">Catalogues</span>', $home);
        $this->assertStringContainsString('<span class="cat-b">Download</span>', $home);
        $this->assertStringContainsString(":class=\"catOpen && 'is-open'\"", $home);
        $this->assertStringContainsString('.nav-cat.is-open .cat-swap > .cat-b', file_get_contents(resource_path('css/app.css')));
        $this->assertStringContainsString('aria-controls="catalogue-menu"', $home);
        $this->assertStringContainsString('aria-haspopup="true"', $home);
        $this->assertStringContainsString('@keydown.escape.window="catOpen = false"', $home);
        $this->assertStringContainsString('@mouseenter="show()" @mouseleave="hide()"', $home);                 // opens on hover, closes when the mouse leaves
        $this->assertStringContainsString("window.matchMedia('(hover: hover)')", $home);                      // touch screens use the button instead
        foreach (['Equipment catalogue' => 'equipment.pdf', 'Consumables catalogue' => 'consumables.pdf'] as $title => $file) {
            $this->assertMatchesRegularExpression('#<a href="[^"]*storage/catalogue/' . preg_quote($file, '#') . '" download#', $home);
            $this->assertStringContainsString($title, $home);
        }
        // phone menu: one button each, named after the catalogue
        $this->assertSame(2, preg_match_all('#class="btn-primary flex h-11 w-full items-center justify-between#', $home));
        $this->assertStringNotContainsString('Product Catalogue', $home);
    }

    public function test_the_contact_page_lists_each_catalogue_and_the_faq_names_them(): void
    {
        SiteSetting::create(['catalogues' => $this->two()]);

        $html = $this->get(route('contact'))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'Download the catalogue as a PDF.'));
        $this->assertStringContainsString('Equipment catalogue, Consumables catalogue', $html);   // in the FAQ answer
        $this->assertStringContainsString('storage/catalogue/consumables.pdf', $html);
    }

    public function test_the_admin_can_save_two_catalogues_and_each_needs_a_name_and_a_file(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $s = SiteSetting::create(['founded_year' => 1993]);

        Livewire::test(EditSiteSetting::class, ['record' => $s->getRouteKey()])
            ->fillForm(['catalogues' => [['title' => '', 'file' => null]]])
            ->call('save')
            ->assertHasFormErrors(['catalogues.0.title' => 'required', 'catalogues.0.file' => 'required']);

        Livewire::test(EditSiteSetting::class, ['record' => $s->getRouteKey()])
            ->assertFormFieldExists('catalogues')
            ->assertFormFieldDoesNotExist('catalogue_file');

        // saving the list itself (the files are stored by the upload field; here two ready-made paths)
        $s->update(['catalogues' => $this->two()]);
        $this->assertCount(2, $s->fresh()->catalogueList());
    }

    public function test_downloads_are_saved_as_agarwal_brothers_and_the_catalogue_name(): void
    {
        $this->assertSame('agarwal-brothers-laboratory-equipment-catalogue.pdf', SiteSetting::downloadName('Laboratory Equipment Catalogue'));
        $this->assertSame('agarwal-brothers-consumables-cateloge.pdf', SiteSetting::downloadName('Consumables Cateloge '));
        $this->assertSame('agarwal-brothers-equipment.pdf', SiteSetting::downloadName('Agarwal Brothers Equipment'));   // not doubled
        $this->assertSame('agarwal-brothers-catalogue.pdf', SiteSetting::downloadName('   '));

        SiteSetting::create(['catalogues' => [
            ['title' => 'Equipment catalogue', 'file' => 'catalogue/01RANDOMULID1.pdf'],
            ['title' => 'Consumables catalogue', 'file' => 'catalogue/01RANDOMULID2.pdf'],
        ]]);

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('download="agarwal-brothers-equipment-catalogue.pdf"', $home);
        $this->assertStringContainsString('download="agarwal-brothers-consumables-catalogue.pdf"', $home);

        $contact = $this->get(route('contact'))->getContent();
        $this->assertStringContainsString('download="agarwal-brothers-equipment-catalogue.pdf"', $contact);
    }
}
