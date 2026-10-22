<?php

namespace App\Filament\Widgets;

use App\Support\AdminFeatures;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;

/** Each person pins the admin areas they use most; they show as buttons under the greeting. */
class MyShortcuts extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.my-shortcuts';

    public static function canView(): bool
    {
        return auth()->check();
    }

    /** The pinned areas that this person can still open. @return list<array{key: string, title: string, url: string}> */
    public function getPinned(): array
    {
        $pins = (array) (auth()->user()?->dashboard_pins ?? []);

        return collect(AdminFeatures::forUser(auth()->user()))->flatten(1)
            ->filter(fn ($item) => in_array($item['key'], $pins, true))
            ->sortBy(fn ($item) => array_search($item['key'], $pins, true))
            ->values()->all();
    }

    public function editShortcutsAction(): Action
    {
        return Action::make('editShortcuts')
            ->label('Edit shortcuts')
            ->icon('heroicon-m-pencil-square')
            ->color('gray')
            ->size('sm')
            ->outlined()
            ->modalHeading('Choose your shortcuts')
            ->modalDescription('Tick the areas you use most. They appear as buttons at the top of your dashboard. This is only for you.')
            ->modalSubmitActionLabel('Save shortcuts')
            ->fillForm(fn () => ['pins' => (array) (auth()->user()?->dashboard_pins ?? [])])
            ->schema([
                CheckboxList::make('pins')
                    ->hiddenLabel()
                    ->options(fn () => AdminFeatures::choices(auth()->user()))
                    ->columns(2)
                    ->searchable(),
            ])
            ->action(function (array $data): void {
                auth()->user()->forceFill(['dashboard_pins' => array_values($data['pins'] ?? [])])->save();

                Notification::make()->title('Shortcuts saved')->success()->send();
            });
    }
}
