<?php

namespace App\Filament\Pages;

use App\Support\HelpGuide as Guide;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/** Plain-English how-to notes for everyone who works in the admin panel. */
class HelpGuide extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'How-to Guide';

    protected static ?string $title = 'How-to Guide';

    protected static ?string $slug = 'help';

    protected static string|\UnitEnum|null $navigationGroup = 'Help';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.help-guide';

    public function getTopics(): array
    {
        return Guide::forUser(auth()->user());
    }
}
