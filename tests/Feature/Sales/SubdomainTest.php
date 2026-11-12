<?php

namespace Tests\Feature\Sales;

use Illuminate\Support\Facades\Route;

/** The Sales panel and the phone API on their own sub-domain (SALES_DOMAIN), and the website's /admin without any Sales screen. */
class SubdomainTest extends SalesTestCase
{
    public function test_the_website_admin_has_no_sales_screens(): void
    {
        $names = collect(Route::getRoutes()->getRoutes())->map(fn ($r) => $r->getName())->filter();

        $this->assertTrue($names->contains('filament.admin.pages.dashboard'));
        $this->assertFalse($names->contains(fn ($n) => str_starts_with($n, 'filament.admin.resources.sales-')), 'Sales screens must not be in /admin');
        $this->assertTrue($names->contains('filament.sales.resources.sales-expenses.index'));
    }

    public function test_a_manager_signs_in_to_the_sales_panel_and_a_rep_cannot(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager)->get('/sales/sales-management')->assertOk();

        $rep = $this->rep('REP001', '9000000001');
        $this->actingAs($rep->user)->get('/sales/sales-management')->assertForbidden();
    }
}
