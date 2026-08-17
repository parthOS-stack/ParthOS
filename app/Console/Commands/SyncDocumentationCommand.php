<?php

namespace App\Console\Commands;

use App\Services\DocumentationSyncService;
use Illuminate\Console\Command;

class SyncDocumentationCommand extends Command
{
    protected $signature = 'docs:sync {--check : Exit with error if docs would change}';

    protected $description = 'Sync DevOS markdown docs from routes and code constants';

    public function handle(DocumentationSyncService $sync): int
    {
        if ($this->option('check')) {
            $beforePages = is_file(base_path('docs/PAGES.md')) ? file_get_contents(base_path('docs/PAGES.md')) : '';
            $beforeFlows = is_file(base_path('docs/FLOWS.md')) ? file_get_contents(base_path('docs/FLOWS.md')) : '';
        }

        $result = $sync->sync();

        if ($this->option('check')) {
            $afterPages = file_get_contents(base_path('docs/PAGES.md'));
            $afterFlows = file_get_contents(base_path('docs/FLOWS.md'));

            if ($beforePages !== $afterPages || $beforeFlows !== $afterFlows) {
                $this->error('Documentation is out of date. Run: php artisan docs:sync');

                return self::FAILURE;
            }

            $this->info('Documentation is up to date.');

            return self::SUCCESS;
        }

        $this->info('DevOS documentation synced at ' . $result['synced_at']);
        $this->line('  - docs/PAGES.md (route table)');
        $this->line('  - docs/FLOWS.md (lockout + OTP constants)');
        $this->line('  - README.md (sync timestamp)');
        $this->newLine();
        $this->comment('Tip: add route descriptions in config/docs.php when you create new routes.');

        return self::SUCCESS;
    }
}
