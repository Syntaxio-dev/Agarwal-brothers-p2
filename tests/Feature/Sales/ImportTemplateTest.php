<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Support\ClientImporter;
use App\Support\ImportTemplate;
use App\Support\ProductImporter;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

/**
 * The import rule (owner, 25 Nov 2026): wherever a CSV or an Excel file can be imported, a template (CSV and Excel) is offered, so a file never
 * fails because of its format; and the importer takes both kinds of file.
 */
class ImportTemplateTest extends SalesTestCase
{
    /** The bytes of a download. */
    private function body($response): string
    {
        ob_start();
        $response->sendContent();

        return (string) ob_get_clean();
    }

    private function saved(string $bytes, string $extension): string
    {
        $path = tempnam(sys_get_temp_dir(), 'abtest') . '.' . $extension;
        file_put_contents($path, $bytes);

        return $path;
    }

    public function test_every_screen_that_takes_a_csv_upload_offers_a_template(): void
    {
        $offenders = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path())) as $file) {
            if ($file->getExtension() !== 'php' || $file->getFilename() === 'ImportTemplate.php') {
                continue;
            }

            $code = file_get_contents($file->getPathname());
            if (str_contains($code, "acceptedFileTypes(['text/csv'") && ! str_contains($code, 'ImportTemplate::download')) {
                $offenders[] = $file->getPathname();
            }
        }

        $this->assertSame([], $offenders, 'A screen that imports a CSV or Excel file must offer the template (App\\Support\\ImportTemplate).');
    }

    public function test_the_client_template_comes_as_csv_and_excel_with_the_columns_the_importer_reads(): void
    {
        $csv = $this->body(ImportTemplate::download('clients-import-template', ClientImporter::sample(), 'csv', ClientImporter::guide()));
        $this->assertStringStartsWith("\xEF\xBB\xBF" . 'Client,Place,Contact,Position,Phone,Email,Notes', $csv, 'a CSV Excel reads as UTF-8');

        $xlsx = $this->saved($this->body(ImportTemplate::download('clients-import-template', ClientImporter::sample(), 'xlsx', ClientImporter::guide())), 'xlsx');
        $this->assertSame('PK', substr(file_get_contents($xlsx), 0, 2), 'a real Excel (.xlsx) file');

        // the first sheet is the template, and the importer finds its Client column and treats the example rows as examples (nothing is imported)
        $csvPath = ImportTemplate::asCsv($xlsx, 'xlsx');
        $summary = ClientImporter::fromCsv($csvPath, null, $this->manager());
        @unlink($csvPath);
        @unlink($xlsx);

        $this->assertSame([], $summary['skipped'], 'the template header is understood');
        $this->assertSame(0, $summary['rows'], 'the example rows are never imported');
        $this->assertSame(0, SalesClient::count());
    }

    public function test_the_excel_template_has_a_how_to_fill_sheet(): void
    {
        $path = $this->saved($this->body(ImportTemplate::download('clients-import-template', ClientImporter::sample(), 'xlsx', ClientImporter::guide())), 'xlsx');
        $reader = new \OpenSpout\Reader\XLSX\Reader;
        $reader->open($path);

        $sheets = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            $rows = [];
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
            $sheets[$sheet->getName()] = $rows;
        }
        $reader->close();
        @unlink($path);

        $this->assertSame(['Template', 'How to fill'], array_keys($sheets));
        $this->assertSame(['Column', 'Needed?', 'What to write', 'Example'], $sheets['How to fill'][0]);
        $this->assertSame('Client', $sheets['How to fill'][1][0]);
        $this->assertSame('Yes', $sheets['How to fill'][1][1]);
    }

    public function test_the_clients_importer_takes_an_excel_file_with_a_number_typed_as_a_number(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'abtest') . '.xlsx';
        $writer = new XlsxWriter;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['Client', 'Place', 'Contact', 'Position', 'Phone']));
        $writer->addRow(Row::fromValues(['Greenlam Pvt Ltd', 'Sitapura', 'Rakesh Purohit', 'Purchase Manager', 9876543210]));   // a number, as Excel keeps it
        $writer->addRow(Row::fromValues(['Greenlam Pvt Ltd', 'Sitapura', 'Meena Joshi', 'Lab Head', '9123456780']));
        $writer->close();

        $csv = ImportTemplate::asCsv($path, 'xlsx');
        $summary = ClientImporter::fromCsv($csv, null, $this->manager());
        @unlink($csv);
        @unlink($path);

        $this->assertSame(1, $summary['clients_created']);
        $this->assertSame(2, $summary['contacts_created']);
        $this->assertEqualsCanonicalizing(['+919876543210', '+919123456780'], SalesContact::pluck('phone_normalized')->all());
    }

    public function test_a_csv_path_is_left_as_it_is(): void
    {
        $this->assertSame('/tmp/file.csv', ImportTemplate::asCsv('/tmp/file.csv', 'csv'));
    }

    public function test_the_products_template_is_offered_in_excel_too_and_its_first_sheet_reads_back(): void
    {
        $guide = array_map(fn (array $c) => [$c[0], $c[1] ? 'Yes' : 'No', $c[2], $c[3]], ProductImporter::guide());
        $path = $this->saved($this->body(ImportTemplate::download('products-import-template', ProductImporter::sample(), 'xlsx', $guide)), 'xlsx');

        $read = ProductImporter::read($path, 'xlsx');
        @unlink($path);

        $this->assertSame(ProductImporter::sample()[0], $read['headers'], 'the importer reads the template it hands out');
        $this->assertCount(count(ProductImporter::sample()) - 1, $read['rows']);
    }
}
