<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget;

/** Filament's "Welcome / sign out" card, moved to the bottom of the dashboard. */
class AccountInfo extends AccountWidget
{
    protected static ?int $sort = 14;
}
