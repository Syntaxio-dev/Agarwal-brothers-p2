<?php

namespace App\Console\Commands;

use App\Support\BackupService;
use Illuminate\Console\Command;

class BackupRun extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Create a full backup (database + uploads) and remove old ones';

    public function handle(): int
    {
        $file = BackupService::full();
        BackupService::prune();
        $this->info('Backup created: ' . basename($file));

        return self::SUCCESS;
    }
}
