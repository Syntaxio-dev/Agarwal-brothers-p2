<?php

namespace App\Filament\Pages;

use App\Support\BackupService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Download or create backups of the database and uploaded files. Administrators only. */
class Backups extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'Backups';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.backups';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->canManage('backups');
    }

    public function getFiles(): array
    {
        return BackupService::list();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('full')
                ->label('Create backup now')
                ->icon(Heroicon::OutlinedCircleStack)
                ->requiresConfirmation()
                ->modalDescription('This saves a full copy (database plus all uploads) on the server. It can take a little while.')
                ->action(function () {
                    $this->guard();
                    BackupService::full();
                    BackupService::prune();
                    Notification::make()->title('Backup created')->success()->send();
                }),
            Action::make('database')
                ->label('Download database')
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => $this->downloadNew('database')),
            Action::make('uploads')
                ->label('Download uploads')
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => $this->downloadNew('uploads')),
        ];
    }

    public function download(string $name): ?StreamedResponse
    {
        $this->guard();
        $path = BackupService::path($name);
        abort_unless($path, 404);

        return response()->streamDownload(function () use ($path) {
            readfile($path);
        }, $name);
    }

    public function remove(string $name): void
    {
        $this->guard();
        BackupService::delete($name);
        Notification::make()->title('Backup deleted')->success()->send();
    }

    protected function downloadNew(string $kind): StreamedResponse
    {
        $this->guard();
        $path = $kind === 'database' ? BackupService::database() : BackupService::uploads();
        BackupService::prune();

        return $this->download(basename($path));
    }

    protected function guard(): void
    {
        abort_unless(static::canAccess(), 403);
    }
}
