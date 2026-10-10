<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Support\ProductImporter;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Upload many products at once from a CSV or Excel file. */
class ImportProducts extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Import products';

    protected static ?string $title = 'Import products';

    protected static ?string $slug = 'import-products';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.import-products';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var array<string, mixed>|null */
    public ?array $result = null;

    public static function canAccess(): bool
    {
        return ProductResource::canCreate();
    }

    public function mount(): void
    {
        $this->form->fill(['update_existing' => false, 'create_categories' => false]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload your file')
                    ->description('One row per product. Use the format shown below the button.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('CSV or Excel file')
                            ->required()
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private')
                            ->maxSize(5120)
                            ->acceptedFileTypes([
                                'text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->helperText('.csv or .xlsx, up to 5 MB and 1000 products. Old .xls files: open them in Excel and use Save As, CSV or xlsx.'),
                        Toggle::make('update_existing')
                            ->label('Update products that already exist')
                            ->helperText('Off: products with the same name in the same category are skipped. On: their filled-in cells replace the saved ones.'),
                        Toggle::make('create_categories')
                            ->label('Create missing categories')
                            ->helperText('Off: a category that does not exist under the brand is reported as an error. On: it is created for you.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        abort_unless(static::canAccess(), 403);

        $state = $this->form->getState();
        $stored = is_array($state['file'] ?? null) ? reset($state['file']) : ($state['file'] ?? null);

        if (! $stored || ! Storage::disk('local')->exists($stored)) {
            Notification::make()->title('Please choose a file first')->danger()->send();

            return;
        }

        $path = Storage::disk('local')->path($stored);

        try {
            $this->result = ProductImporter::import(
                $path,
                pathinfo($stored, PATHINFO_EXTENSION),
                (bool) ($state['update_existing'] ?? false),
                (bool) ($state['create_categories'] ?? false),
            );
        } finally {
            Storage::disk('local')->delete($stored);   // the file is not kept
        }

        $this->form->fill(['update_existing' => (bool) ($state['update_existing'] ?? false), 'create_categories' => (bool) ($state['create_categories'] ?? false)]);

        if ($this->result['fatal'] || $this->result['errors']) {
            Notification::make()->title('Nothing was imported')->body('Fix the points listed below and upload the file again.')->danger()->send();

            return;
        }

        Notification::make()
            ->title('Import finished')
            ->body("{$this->result['created']} created, {$this->result['updated']} updated, {$this->result['skipped']} skipped.")
            ->success()
            ->send();
    }

    /** Template with example rows (opens in Excel). */
    public function downloadSample(): StreamedResponse
    {
        abort_unless(static::canAccess(), 403);

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // lets Excel read the file as UTF-8
            foreach (ProductImporter::sample() as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'products-import-sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
