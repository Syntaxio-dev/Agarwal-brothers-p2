<?php

namespace Tests\Feature;

use App\Filament\Pages\ImportProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\ProductImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $f) {
            @unlink($f);
        }
        parent::tearDown();
    }

    private function csv(string $content, string $ext = 'csv'): string
    {
        $path = tempnam(sys_get_temp_dir(), 'abimp') . '.' . $ext;
        file_put_contents($path, $content);

        return $this->files[] = $path;
    }

    private function catalogue(): Category
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);

        return Category::create(['brand_id' => $brand->id, 'name' => 'Analytical Balances', 'slug' => 'analytical-balances']);
    }

    private const HEADER = "name,brand,category,model_group,short_description,specs,features,is_active,spec: Capacity,spec: Readability\n";

    public function test_rows_become_draft_products_with_specs_features_and_spec_columns(): void
    {
        $cat = $this->catalogue();
        $file = $this->csv(self::HEADER
            . "Entris II Essential,Sartorius,Analytical Balances,Standard Models,Compact balance,Pan size: 90 mm | Display: LCD,Fast: Quick reading | Compact,no,220 g,0.1 mg\n"
            . "Entris II Advanced,sartorius, analytical  balances ,Advanced Models,Better,,,yes,620 g,\n");

        $r = ProductImporter::import($file, 'csv');

        $this->assertSame([2, 0, 0, []], [$r['created'], $r['updated'], $r['skipped'], $r['errors']]);

        $a = Product::where('name', 'Entris II Essential')->firstOrFail();
        $this->assertSame($cat->id, $a->category_id);
        $this->assertFalse($a->is_active);                       // drafts by default
        $this->assertSame('entris-ii-essential', $a->slug);
        $this->assertSame(['Pan size' => '90 mm', 'Display' => 'LCD', 'Capacity' => '220 g', 'Readability' => '0.1 mg'], $a->specs);
        $this->assertSame([['title' => 'Fast', 'text' => 'Quick reading'], ['title' => 'Compact', 'text' => '']], $a->features);

        $b = Product::where('name', 'Entris II Advanced')->firstOrFail();
        $this->assertTrue($b->is_active);
        $this->assertSame(['Capacity' => '620 g'], $b->specs);     // different number of specs is fine
    }

    public function test_any_bad_row_stops_the_whole_import_and_lists_the_problems(): void
    {
        $this->catalogue();
        $file = $this->csv(self::HEADER
            . "Good Product,Sartorius,Analytical Balances,,,,,,,\n"
            . "Bad Brand,Nobody,Analytical Balances,,,,,,,\n"
            . "Bad Category,Sartorius,Missing Line,,,,,,,\n"
            . ",Sartorius,Analytical Balances,,,,,,,\n"
            . "Bad Spec,Sartorius,Analytical Balances,,,Capacity 220 g,,,,\n"
            . "Bad Active,Sartorius,Analytical Balances,,,,,maybe,,\n");

        $r = ProductImporter::import($file, 'csv');

        $this->assertSame(0, Product::count());                  // nothing saved, not even the good row
        $this->assertCount(5, $r['errors']);
        $this->assertSame([3, 4, 5, 6, 7], collect($r['errors'])->pluck('line')->all());
        $this->assertStringContainsString('Brand "Nobody" does not exist', $r['errors'][0]['message']);
        $this->assertStringContainsString('Category "Missing Line" does not exist', $r['errors'][1]['message']);
        $this->assertStringContainsString('name is empty', $r['errors'][2]['message']);
        $this->assertStringContainsString('Name: Value', $r['errors'][3]['message']);
        $this->assertStringContainsString('yes or no', $r['errors'][4]['message']);
    }

    public function test_missing_required_columns_and_duplicate_rows_are_reported(): void
    {
        $this->catalogue();

        $r = ProductImporter::import($this->csv("name,brand\nX,Sartorius\n"), 'csv');
        $this->assertStringContainsString('Missing: category', $r['fatal']);

        $dup = ProductImporter::import($this->csv("name,brand,category\nA,Sartorius,Analytical Balances\na,Sartorius,Analytical Balances\n"), 'csv');
        $this->assertStringContainsString('twice', $dup['errors'][0]['message']);
        $this->assertSame(0, Product::count());
    }

    public function test_existing_products_are_skipped_or_updated_on_request(): void
    {
        $cat = $this->catalogue();
        $existing = Product::create(['category_id' => $cat->id, 'name' => 'Entris II', 'slug' => 'entris-ii', 'is_active' => true, 'short_description' => 'Old text', 'specs' => ['Old' => '1']]);
        $file = $this->csv("name,brand,category,short_description,specs\nEntris II,Sartorius,Analytical Balances,New text,Capacity: 220 g\nBrand New,Sartorius,Analytical Balances,Hello,\n");

        $skip = ProductImporter::import($file, 'csv');
        $this->assertSame([1, 0, 1], [$skip['created'], $skip['updated'], $skip['skipped']]);
        $this->assertSame('Old text', $existing->fresh()->short_description);

        $upd = ProductImporter::import($file, 'csv', updateExisting: true);
        $this->assertSame([0, 2], [$upd['created'], $upd['updated']]);      // both products exist now (the second was created by the first run)
        $this->assertSame('New text', $existing->fresh()->short_description);
        $this->assertSame(['Capacity' => '220 g'], $existing->fresh()->specs);
        $this->assertTrue($existing->fresh()->is_active);                 // not mentioned in the file, so untouched
    }

    public function test_missing_categories_can_be_created(): void
    {
        $this->catalogue();
        $file = $this->csv("name,brand,category\nP1,Sartorius,Brand New Line\nP2,Sartorius,brand new line\n");

        $this->assertNotEmpty(ProductImporter::import($file, 'csv')['errors']);

        $r = ProductImporter::import($file, 'csv', createCategories: true);
        $this->assertSame(2, $r['created']);
        $this->assertSame(1, Category::where('name', 'Brand New Line')->count());   // created once, shared by both rows
    }

    public function test_excel_files_semicolon_files_and_excel_bom_are_read(): void
    {
        $this->catalogue();

        // .xlsx
        $xlsx = tempnam(sys_get_temp_dir(), 'abx') . '.xlsx';
        $this->files[] = $xlsx;
        $w = new XlsxWriter;
        $w->openToFile($xlsx);
        $w->addRow(Row::fromValues(['Name', 'Brand', 'Category', 'Specs', 'Active']));
        $w->addRow(Row::fromValues(['Excel Product', 'Sartorius', 'Analytical Balances', 'Capacity: 5 kg', 'yes']));
        $w->close();

        $r = ProductImporter::import($xlsx, 'xlsx');
        $this->assertSame(1, $r['created'], json_encode($r));
        $this->assertTrue(Product::where('name', 'Excel Product')->firstOrFail()->is_active);

        // semicolon separated + BOM (what Excel saves in many regions)
        $semi = $this->csv("\xEF\xBB\xBFname;brand;category\nSemi Product;Sartorius;Analytical Balances\n");
        $r = ProductImporter::import($semi, 'csv');
        $this->assertSame(1, $r['created'], json_encode($r));
    }

    public function test_unreadable_and_oversized_files_give_a_clear_message(): void
    {
        $this->catalogue();

        $bad = $this->csv('not a spreadsheet', 'xlsx');
        $this->assertStringContainsString('could not be read', ProductImporter::import($bad, 'xlsx')['fatal']);

        $rows = "name,brand,category\n" . str_repeat("X,Sartorius,Analytical Balances\n", ProductImporter::MAX_ROWS + 1);
        $this->assertStringContainsString('more than 1000', ProductImporter::import($this->csv($rows), 'csv')['fatal']);
    }

    public function test_the_downloadable_sample_imports_without_changes(): void
    {
        $this->catalogue();

        $out = fopen('php://temp', 'w+');
        foreach (ProductImporter::sample() as $row) {
            fputcsv($out, $row);
        }
        rewind($out);
        $file = $this->csv(stream_get_contents($out));

        $r = ProductImporter::import($file, 'csv');

        $this->assertSame([], $r['errors'], json_encode($r));
        $this->assertSame(3, $r['created']);
    }

    public function test_page_upload_flow_shows_the_result_and_removes_the_file(): void
    {
        $this->catalogue();
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        $good = UploadedFile::fake()->createWithContent('products.csv', "name,brand,category\nFrom Page,Sartorius,Analytical Balances\n");

        Livewire::test(ImportProducts::class)
            ->fillForm(['file' => $good])
            ->call('import')
            ->assertHasNoFormErrors()
            ->assertSee('Import finished')
            ->assertSee('Download sample file')
            ->assertSee('How to prepare your CSV or Excel file');

        $this->assertDatabaseHas('products', ['name' => 'From Page', 'is_active' => false]);
        $this->assertSame([], Storage::disk('local')->allFiles('imports'));        // the upload is not kept

        $bad = UploadedFile::fake()->createWithContent('bad.csv', "name,brand,category\nOops,Unknown Brand,X\n");
        Livewire::test(ImportProducts::class)
            ->fillForm(['file' => $bad])
            ->call('import')
            ->assertSee('Nothing was imported')
            ->assertSee('Unknown Brand');
        $this->assertDatabaseMissing('products', ['name' => 'Oops']);
    }

    public function test_sample_file_download_works_from_the_page(): void
    {
        $this->catalogue();
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(ImportProducts::class)
            ->assertSee('Upload your file')->assertSee('CSV or Excel file')
            ->call('downloadSample')
            ->assertFileDownloaded('products-import-sample.csv');
    }

    public function test_only_people_who_can_create_products_get_the_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hr', 'is_active' => true]));
        $this->get('/admin/import-products')->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'editor', 'is_active' => true]));
        $this->get('/admin/import-products')->assertOk()->assertSee('Download sample file');
    }
}
