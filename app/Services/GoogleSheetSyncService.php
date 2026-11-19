<?php

namespace App\Services;

use App\Support\DailyLeadTabParser;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class GoogleSheetSyncService
{
    private const ROW_LIMIT = 5000;

    private const LAST_COLUMN = 'AZ';

    private const LOCK_NAME = 'lead-google-sheets-sync';

    private const LOCK_SECONDS = 600;

    public function __construct(
        private readonly LeadImportService $leadImporter,
        private readonly DailyLeadTabParser $dailyLeadTabParser,
    ) {}

    /** @return array{enriched: int, inserted: int, skipped: int, resurfaced: int, unassigned: int, needs_review: int, missing_dates: int, missing_source_fields: int, failed: int, partial: int} */
    public function sync(?int $actorId = null, ?int $branchId = null): array
    {
        $lock = Cache::lock(self::LOCK_NAME, self::LOCK_SECONDS);
        if (! $lock->get()) {
            throw new RuntimeException('Sinkronisasi Google Sheets sedang berjalan. Coba lagi beberapa saat lagi.');
        }

        try {
            return $this->syncUnlocked($actorId, $branchId);
        } finally {
            $lock->release();
        }
    }

    /** @return array{enriched: int, inserted: int, skipped: int, resurfaced: int, unassigned: int, needs_review: int, missing_dates: int, missing_source_fields: int, failed: int, partial: int} */
    private function syncUnlocked(?int $actorId, ?int $branchId): array
    {
        $sources = DB::table('branches')
            ->whereNotNull('google_spreadsheet_id')
            ->when($branchId, fn ($query) => $query->where('id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name', 'google_spreadsheet_id']);
        if ($sources->isEmpty()) {
            throw new RuntimeException($branchId ? 'Cabang ini belum memiliki Spreadsheet ID.' : 'Belum ada Spreadsheet ID pada konfigurasi cabang.');
        }

        $credentialsPath = (string) config('leads.google_sheets.credentials_path');
        if ($credentialsPath === '' || ! is_readable($credentialsPath)) {
            $message = 'File kredensial Google Sheets belum dikonfigurasi di server.';
            $this->recordFailures($this->sourceIds($sources), $message);
            $this->record('failed', null, $message);

            throw new RuntimeException($message);
        }

        try {
            $accessToken = $this->accessToken($credentialsPath);
        } catch (Throwable $exception) {
            $message = $this->failureMessage($exception);
            $this->recordFailures($this->sourceIds($sources), $message);
            $this->record('failed', null, $message);

            throw new RuntimeException($message, previous: $exception);
        }

        $client = Http::withToken($accessToken)->acceptJson()->timeout(25)->retry(3, 500);
        $summary = ['enriched' => 0, 'inserted' => 0, 'skipped' => 0, 'resurfaced' => 0, 'unassigned' => 0, 'needs_review' => 0, 'missing_dates' => 0, 'missing_source_fields' => 0, 'failed' => 0, 'partial' => 0];
        $failures = [];
        foreach ($sources as $source) {
            try {
                $sourceData = $this->readBranch($client, (string) $source->google_spreadsheet_id);
                $sourceSummary = DB::transaction(function () use ($actorId, $source, $sourceData): array {
                    $importSummary = $this->leadImporter->import(
                        $sourceData['rows'],
                        $actorId,
                        (string) $source->google_spreadsheet_id,
                        (int) $source->id,
                    );
                    $partialError = $sourceData['errors'] === [] ? null : implode(' ', $sourceData['errors']);
                    $branchSummary = [...$importSummary, 'missing_source_fields' => $sourceData['missing_source_fields'], 'failed_tabs' => count($sourceData['errors'])];

                    DB::table('branches')->where('id', $source->id)->update([
                        'google_sheets_synced_at' => now(),
                        'google_sheets_sync_error' => $partialError,
                    ]);
                    $this->record($partialError === null ? 'success' : 'partial', $branchSummary, $partialError, (int) $source->id);

                    return $branchSummary;
                });
                foreach (['enriched', 'inserted', 'skipped', 'resurfaced', 'unassigned', 'needs_review', 'missing_dates', 'missing_source_fields'] as $key) {
                    $summary[$key] += $sourceSummary[$key];
                }
                $summary['partial'] += $sourceSummary['failed_tabs'] > 0 ? 1 : 0;
            } catch (Throwable $exception) {
                $message = $this->failureMessage($exception);
                $this->recordFailure((int) $source->id, $message);
                $failures[] = "{$source->name}: {$message}";
                $summary['failed']++;
            }
        }

        if ($summary['failed'] === $sources->count()) {
            $message = $branchId
                ? "Sinkronisasi cabang gagal. {$failures[0]}"
                : 'Tidak ada cabang yang berhasil disinkronkan. '.implode(' ', $failures);
            $this->record('failed', $summary, $message);

            throw new RuntimeException($message);
        }
        $this->record($summary['failed'] || $summary['partial'] ? 'partial' : 'success', $summary);

        return $summary;
    }

    /** @return array{rows: list<array<string, string|null>>, missing_source_fields: int, errors: list<string>} */
    protected function readBranch(PendingRequest $client, string $spreadsheetId): array
    {
        $metadata = $client->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}?fields=sheets.properties")->throw()->json();
        $sheets = is_array($metadata) && is_array($metadata['sheets'] ?? null) ? $metadata['sheets'] : [];
        /** @var list<string> $tabs */
        $tabs = collect($sheets)->map(fn (mixed $sheet) => $sheet['properties']['title'] ?? null)
            ->filter(fn (mixed $title) => is_string($title) && preg_match('/^\d{1,2}$/', $title) === 1 && (int) $title >= 1 && (int) $title <= 31)
            ->sortBy(fn (string $title) => (int) $title)->values()->all();
        if ($tabs === []) {
            throw new RuntimeException('Tidak menemukan tab harian bernama angka, misalnya 1 sampai 31.');
        }
        $ranges = array_map(fn (string $tab) => "{$tab}!A1:".self::LAST_COLUMN.self::ROW_LIMIT, $tabs);
        $query = implode('&', array_map(fn (string $range) => 'ranges='.rawurlencode($range), $ranges));
        $response = $client->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values:batchGet?{$query}")->throw()->json('valueRanges', []);
        $valueRanges = is_array($response) ? $response : [];
        $tabValues = [];
        foreach ($tabs as $index => $tab) {
            $tabValues[$tab] = is_array($valueRanges[$index]['values'] ?? null) ? $valueRanges[$index]['values'] : [];
        }
        $parsed = $this->dailyLeadTabParser->parseAll($tabValues);
        if ($parsed['rows'] === [] && $parsed['errors'] !== []) {
            throw new RuntimeException(implode(' ', $parsed['errors']));
        }
        if (count($parsed['rows']) > self::ROW_LIMIT) {
            throw new RuntimeException('Maksimal 5.000 lead total dari seluruh tab harian.');
        }

        return $parsed;
    }

    protected function accessToken(string $credentialsPath): string
    {
        $token = (new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/spreadsheets.readonly'],
            $credentialsPath,
        ))->fetchAuthToken();
        $accessToken = $token['access_token'] ?? null;
        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Token Google Sheets tidak dapat dibuat.');
        }

        return $accessToken;
    }

    /** @param array<int, int> $branchIds */
    private function recordFailures(array $branchIds, string $message): void
    {
        foreach ($branchIds as $branchId) {
            $this->recordFailure($branchId, $message);
        }
    }

    /** @param iterable<object> $sources
     * @return array<int, int>
     */
    private function sourceIds(iterable $sources): array
    {
        $branchIds = [];
        foreach ($sources as $source) {
            $branchIds[] = (int) data_get($source, 'id');
        }

        return $branchIds;
    }

    private function recordFailure(int $branchId, string $message): void
    {
        DB::transaction(function () use ($branchId, $message): void {
            DB::table('branches')->where('id', $branchId)->update(['google_sheets_sync_error' => $message]);
            $this->record('failed', null, $message, $branchId);
        });
    }

    private function failureMessage(Throwable $exception): string
    {
        if ($exception instanceof RuntimeException) {
            return $exception->getMessage();
        }
        if ($exception instanceof RequestException) {
            return match ($exception->response->status()) {
                401, 403 => 'Google Sheets tidak dapat diakses. Pastikan spreadsheet dibagikan ke email Service Account.',
                404 => 'Spreadsheet tidak ditemukan. Periksa ID spreadsheet pada cabang ini.',
                default => 'Google Sheets sementara tidak dapat diakses. Coba lagi beberapa saat lagi.',
            };
        }

        return 'Sistem tidak dapat menyimpan hasil sinkronisasi. Coba lagi.';
    }

    /** @param array<string, int>|null $summary */
    private function record(string $status, ?array $summary = null, ?string $error = null, ?int $branchId = null): void
    {
        DB::table('lead_sync_runs')->insert([
            'branch_id' => $branchId,
            'source' => 'google_sheets',
            'status' => $status,
            'summary' => $summary ? json_encode($summary, JSON_THROW_ON_ERROR) : null,
            'error' => $error,
            'created_at' => now(),
            'completed_at' => now(),
        ]);
    }
}
