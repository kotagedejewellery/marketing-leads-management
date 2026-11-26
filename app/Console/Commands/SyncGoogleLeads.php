<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetSyncService;
use Illuminate\Console\Command;
use RuntimeException;

class SyncGoogleLeads extends Command
{
    protected $signature = 'leads:sync-google-sheet';

    protected $description = 'Read daily numeric tabs from Google Sheets and import new leads';

    public function handle(GoogleSheetSyncService $sync): int
    {
        try {
            $summary = $sync->sync();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("{$summary['inserted']} baru, {$summary['enriched']} diperbarui, {$summary['resurfaced']} muncul kembali, {$summary['awaiting_account']} menunggu akun CS, {$summary['missing_source_fields']} kolom sumber kosong.");

        return self::SUCCESS;
    }
}
