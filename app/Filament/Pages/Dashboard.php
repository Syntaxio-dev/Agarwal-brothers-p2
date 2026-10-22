<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\ToggleButtons;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

/**
 * The admin home. Same page as Filament's, with a grid that is one column on phones and two from large screens,
 * and one date-range switch (7 / 30 / 90 days) that the trend, source, product and response numbers follow.
 */
class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 2];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                ToggleButtons::make('range')
                    ->label('Show numbers for')
                    ->options(collect(config('dashboard.ranges'))->mapWithKeys(fn ($d) => [(string) $d => "Last {$d} days"])->all())
                    ->default((string) config('dashboard.default_range'))
                    ->inline()
                    ->live(),
            ]);
    }
}
