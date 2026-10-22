<?php

namespace Tests\Feature;

use App\Console\Commands\LoadBrandDetails;
use App\Filament\Pages\ImportProducts;
use App\Filament\Resources\Brands\BrandResource;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Widgets\OverviewStats;
use App\Http\Controllers\SeoController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function as(string $role): User
    {
        $user = User::factory()->create(['role' => $role, 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    private function product(string $name = 'BSA224S'): Product
    {
        $brand = Brand::firstOrCreate(['slug' => 'sartorius'], ['name' => 'Sartorius', 'is_active' => true]);
        $cat = Category::firstOrCreate(['slug' => 'bsa'], ['brand_id' => $brand->id, 'name' => 'BSA']);

        return Product::create(['category_id' => $cat->id, 'name' => $name, 'slug' => str($name)->slug()->toString(), 'is_active' => true]);
    }

    // ------------------------------------------------------------------ public site

    public function test_text_parameters_sent_as_arrays_never_crash_a_page(): void
    {
        foreach (['/search?q[]=x', '/search/suggest?q[]=x', '/compare?p[]=x', '/careers?role[]=x', '/contact-us?topic[]=x'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_an_enquiry_with_array_values_is_rejected_cleanly(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $p = $this->product();

        $this->post(route('enquiry.store'), ['product_id' => [$p->id], 'name' => 'Ravi Sharma', 'email' => 'r@example.com'])
            ->assertRedirect()->assertSessionHasErrors('product_id');

        $this->post(route('enquiry.store'), ['product_id' => $p->id, 'name' => 'Ravi Sharma', 'email' => 'r@example.com', 'answers' => [5 => ['x']]])
            ->assertRedirect();
        $this->assertSame(0, Enquiry::where('product_id', '!=', $p->id)->count());
    }

    public function test_each_form_has_its_own_rate_limit_so_searching_never_blocks_an_enquiry(): void
    {
        $p = $this->product();

        foreach (range(1, 20) as $i) {
            $this->getJson('/search/suggest?q=bs' . $i)->assertOk();
        }

        // 5 enquiries a minute are fine even after all that searching ...
        foreach (range(1, 5) as $i) {
            $this->post(route('enquiry.store'), ['product_id' => $p->id, 'name' => 'Ravi Sharma', 'email' => "r{$i}@example.com"])->assertRedirect();
        }
        // ... the 6th is refused
        $this->post(route('enquiry.store'), ['product_id' => $p->id, 'name' => 'Ravi Sharma', 'email' => 'r6@example.com'])->assertStatus(429);

        // and the contact form still has its own counter
        $this->get('/contact-us')->assertOk();
    }

    public function test_comparisons_count_only_for_real_visitors_once_per_address_per_day(): void
    {
        $a = $this->product('BSA224S');
        $b = $this->product('BSA124S');
        $url = route('compare', ['p' => $a->slug . ',' . $b->slug]);

        // a bot without any cookie is not counted
        $this->get($url)->assertOk();
        $this->assertSame(0, ProductComparison::count());

        // a visitor with a session is counted once ...
        $this->withCookie(config('session.cookie'), 'abc')->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->get($url)->assertOk();
        $this->assertSame(1, (int) ProductComparison::where('product_id', $a->id)->value('times'));

        // ... and a fresh session from the same address on the same day is not counted again
        $this->flushSession();
        $this->withCookie(config('session.cookie'), 'def')->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->get($url)->assertOk();
        $this->assertSame(1, (int) ProductComparison::where('product_id', $a->id)->value('times'));
    }

    public function test_a_typed_role_cannot_carry_a_link_into_the_auto_reply(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        Storage::fake('local');

        $response = $this->post(route('careers.apply.general'), [
            'name' => 'Ravi Sharma', 'email' => 'r@example.com', 'phone' => '9876543210', 'phone_country' => 'IN',
            'position' => config('careers.other', 'Other'), 'position_other' => 'Visit https://evil.example now',
            'location' => 'Jaipur', 'department' => 'Sales',
            'resume' => \Illuminate\Http\UploadedFile::fake()->create('cv.pdf', 20, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('position_other');
    }

    public function test_a_hidden_product_drops_out_of_the_cached_sitemap_at_once(): void
    {
        $p = $this->product();
        $this->get('/sitemap.xml')->assertSee('/products/' . $p->slug);
        $this->assertTrue(Cache::has(SeoController::cacheKey()));

        $p->update(['is_active' => false]);

        $this->get('/sitemap.xml')->assertDontSee('/products/' . $p->slug);
    }

    public function test_an_svg_logo_that_can_run_script_is_never_copied(): void
    {
        $dir = sys_get_temp_dir();
        file_put_contents($bad = $dir . '/bad-logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>');
        file_put_contents($good = $dir . '/good-logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h10v10z"/></svg>');

        $this->assertFalse(LoadBrandDetails::safeLogo($bad));
        $this->assertTrue(LoadBrandDetails::safeLogo($good));
    }

    // ------------------------------------------------------------------ admin panel

    public function test_sales_cannot_add_categories_or_brands_from_the_product_form(): void
    {
        $this->as('sales');
        Livewire::test(CreateProduct::class)
            ->assertActionHidden(TestAction::make('createOption')->schemaComponent('category_id', schema: 'form'));

        $this->as('editor');
        Livewire::test(CreateProduct::class)
            ->assertActionVisible(TestAction::make('createOption')->schemaComponent('category_id', schema: 'form'));
    }

    public function test_the_import_page_ignores_a_tampered_file_path_and_never_deletes_other_files(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('resumes/someone.pdf', 'private');
        $this->as('editor');

        Livewire::test(ImportProducts::class)
            ->set('data.file', ['x' => 'resumes/someone.pdf'])
            ->call('check');

        Storage::disk('local')->assertExists('resumes/someone.pdf');
    }

    public function test_only_people_who_may_add_categories_can_let_the_import_create_them(): void
    {
        $this->as('sales');
        $this->assertFalse(ImportProducts::mayCreateCategories());

        $this->as('editor');
        $this->assertTrue(ImportProducts::mayCreateCategories());
    }

    public function test_every_filament_action_follows_the_role_rules(): void
    {
        $this->as('sales');
        $this->assertFalse(BrandResource::can('create'));
        $this->assertTrue(ProductResource::can('create'));
        $this->assertFalse(ProductResource::can('forceDelete', $this->product()));

        $this->as('admin');
        $setting = SiteSetting::create([]);
        $this->assertFalse(SiteSettingResource::can('delete', $setting));
        $this->assertTrue(SiteSettingResource::can('update', $setting));
    }

    public function test_an_admin_cannot_change_their_own_role_even_by_tampering(): void
    {
        $me = $this->as('admin');
        User::factory()->create(['role' => 'admin', 'is_active' => true]);   // another admin, so the last-admin rule is not what stops it

        Livewire::test(EditUser::class, ['record' => $me->getKey()])
            ->set('data.role', 'hr')
            ->set('data.is_active', false)
            ->call('save');

        $this->assertSame('admin', $me->fresh()->role);
        $this->assertTrue($me->fresh()->is_active);
    }

    public function test_editors_do_not_see_the_enquiry_count(): void
    {
        $this->as('editor');
        Livewire::test(OverviewStats::class)->assertDontSee('New enquiries')->assertSee('Active products');

        $this->as('admin');
        Livewire::test(OverviewStats::class)->assertSee('New enquiries');
    }

    public function test_the_upload_folder_only_serves_upload_file_types(): void
    {
        $rules = file_get_contents(storage_path('app/public/.htaccess'));

        $this->assertStringContainsString('Require all denied', $rules);
        $this->assertMatchesRegularExpression('/jpe\?g\|png\|webp\|gif\|avif\|svg\|pdf\|mp4\|webm/', $rules);
    }

    public function test_uploads_on_the_public_disk_are_named_by_their_real_content(): void
    {
        // real files on disk, so the type is read from their content (like a real upload), not from the name
        $real = function (string $name, string $content) {
            $path = sys_get_temp_dir() . '/' . uniqid() . '-' . $name;
            file_put_contents($path, $content);

            return new \Symfony\Component\HttpFoundation\File\UploadedFile($path, $name, null, null, true);
        };
        $pdfAsHtml = $real('page.html', "%PDF-1.4
1 0 obj<<>>endobj
trailer<<>>
%%EOF");
        $htmlAsJpg = $real('photo.jpg', '<html><body><script>alert(1)</script></body></html>');
        $csv = $real('products.csv', "name,brand
A,B
");

        $this->assertStringEndsWith('.pdf', \App\Filament\Support\Uploads::storageName($pdfAsHtml, 'public'));
        $this->assertStringEndsWith('.bin', \App\Filament\Support\Uploads::storageName($htmlAsJpg, 'public'));
        $this->assertStringEndsWith('.csv', \App\Filament\Support\Uploads::storageName($csv, 'local'));
        $this->assertDoesNotMatchRegularExpression('/page|photo|products/', \App\Filament\Support\Uploads::storageName($csv, 'local'));
    }
}
