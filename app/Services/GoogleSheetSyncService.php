<?php

namespace App\Services;

use App\Support\DailyLeadTabParser;
use App\Support\LeadSourceDate;
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

    /** @return array{enriched: int, inserted: int, skipped: int, resurfaced: int, unassigned: int, awaiting_account: int, needs_review: int, missing_dates: int, missing_source_fields: int, failed: int, partial: int} */
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

    /** @return array{rows: int, matching: int, new: int, updates: int, conflicts: int, missing_source_fields: int, failed_tabs: int} */
    public function previewReplacement(int $branchId, string $spreadsheetId): array
    {
        $lock = Cache::lock(self::LOCK_NAME, self::LOCK_SECONDS);
        if (! $lock->get()) {
            throw new RuntimeException('Sinkronisasi Google Sheets sedang berjalan. Coba lagi beberapa saat lagi.');
        }

        try {
            abort_unless(DB::table('branches')->where('id', $branchId)->exists(), 404);
            $sourceData = $this->readBranch($this->googleClient(), $spreadsheetId);

            return [
                ...$this->leadImporter->preview($sourceData['rows'], $branchId),
                'missing_source_fields' => $sourceData['missing_source_fields'],
                'failed_tabs' => count($sourceData['errors']),
            ];
        } finally {
            $lock->release();
        }
    }

    /** @return array{spreadsheet_id: string, changes: list<array{lead_id: int, contact: string, tab: string, old_date: string|null, old_source_date: string|null, old_revision: int, new_date: string, old_cs: string|null, new_cs: string}>, matched: int, conflicts: int, uncertain: int, review: list<array{contact: string, tab: string, reason: string}>, failed_tabs: int} */
    public function previewReconciliation(int $branchId): array
    {
        $lock = Cache::lock(self::LOCK_NAME, self::LOCK_SECONDS);
        if (! $lock->get()) {
            throw new RuntimeException('Sinkronisasi Google Sheets sedang berjalan. Coba lagi beberapa saat lagi.');
        }
        try {
            return $this->reconciliationPlan($branchId);
        } finally {
            $lock->release();
        }
    }

    public function applyReconciliation(int $branchId, int $actorId, string $expectedHash): int
    {
        $lock = Cache::lock(self::LOCK_NAME, self::LOCK_SECONDS);
        if (! $lock->get()) {
            throw new RuntimeException('Sinkronisasi Google Sheets sedang berjalan. Coba lagi beberapa saat lagi.');
        }
        try {
            $plan = $this->reconciliationPlan($branchId);
            if ($plan['failed_tabs'] > 0 || $plan['changes'] === []
                || ! hash_equals($expectedHash, hash('sha256', json_encode($plan, JSON_THROW_ON_ERROR)))) {
                throw new RuntimeException('Sumber atau data lead berubah, atau ada tab gagal dibaca. Buat pratinjau baru.');
            }

            return $this->leadImporter->applyReconciliation($plan['changes'], $branchId, $actorId);
        } finally {
            $lock->release();
        }
    }

    /** @return array{spreadsheet_id: string, changes: list<array{lead_id: int, contact: string, tab: string, old_date: string|null, old_source_date: string|null, old_revision: int, new_date: string, old_cs: string|null, new_cs: string}>, matched: int, conflicts: int, uncertain: int, review: list<array{contact: string, tab: string, reason: string}>, failed_tabs: int} */
    private function reconciliationPlan(int $branchId): array
    {
        $spreadsheetId = DB::table('branches')->where('id', $branchId)->value('google_spreadsheet_id');
        if (! is_string($spreadsheetId) || $spreadsheetId === '') {
            throw new RuntimeException('Cabang ini belum memiliki spreadsheet sumber.');
        }
        $sourceData = $this->readBranch($this->googleClient(), $spreadsheetId);

        return ['spreadsheet_id' => $spreadsheetId, ...$this->leadImporter->previewReconciliation($sourceData['rows'], $branchId), 'failed_tabs' => count($sourceData['errors'])];
    }

    /** @return array{enriched: int, inserted: int, skipped: int, resurfaced: int, unassigned: int, awaiting_account: int, needs_review: int, missing_dates: int, missing_source_fields: int, failed: int, partial: int} */
    public function replaceSource(?int $actorId, int $branchId, string $spreadsheetId): array
    {
        $lock = Cache::lock(self::LOCK_NAME, self::LOCK_SECONDS);
        if (! $lock->get()) {
            throw new RuntimeException('Sinkronisasi Google Sheets sedang berjalan. Coba lagi beberapa saat lagi.');
        }

        try {
            $branch = DB::table('branches')->where('id', $branchId)->first([
                'id',
                'google_spreadsheet_id',
                'google_sheets_synced_at',
                'google_sheets_sync_error',
            ]) ?? abort(404);
            DB::table('branches')->where('id', $branchId)->update([
                'google_spreadsheet_id' => $spreadsheetId,
                'google_sheets_synced_at' => null,
                'google_sheets_sync_error' => null,
            ]);

            try {
                $summary = $this->syncUnlocked($actorId, $branchId, true);
            } catch (Throwable $exception) {
                DB::table('branches')->where('id', $branchId)->update([
                    'google_spreadsheet_id' => $branch->google_spreadsheet_id,
                    'google_sheets_synced_at' => $branch->google_sheets_synced_at,
                    'google_sheets_sync_error' => $branch->google_sheets_sync_error,
                ]);
                $this->recordSourceEvent($branchId, $actorId, 'replacement_failed', $branch->google_spreadsheet_id, $spreadsheetId, $this->failureMessage($exception));

                throw $exception instanceof RuntimeException
                    ? $exception
                    : new RuntimeException($this->failureMessage($exception), previous: $exception);
            }

            $this->recordSourceEvent($branchId, $actorId, 'replaced', $branch->google_spreadsheet_id, $spreadsheetId, "{$summary['inserted']} lead baru, {$summary['enriched']} data sumber diperbarui.");

            return $summary;
        } finally {
            $lock->release();
        }
    }

    public function disconnect(?int $actorId, int $branchId): void
    {
        $lock = Cache::lock(self::LOCK_NAME, self::LOCK_SECONDS);
        if (! $lock->get()) {
            throw new RuntimeException('Sinkronisasi Google Sheets sedang berjalan. Coba lagi beberapa saat lagi.');
        }

        try {
            $branch = DB::table('branches')->where('id', $branchId)->first(['id', 'google_spreadsheet_id']) ?? abort(404);
            DB::table('branches')->where('id', $branchId)->update([
                'google_spreadsheet_id' => null,
                'google_sheets_synced_at' => null,
                'google_sheets_sync_error' => null,
            ]);
            if ($branch->google_spreadsheet_id) {
                $this->recordSourceEvent($branchId, $actorId, 'disconnected', $branch->google_spreadsheet_id, null, 'Sinkronisasi Google Sheets dihentikan.');
            }
        } finally {
            $lock->release();
        }
    }

    /** @return array{enriched: int, inserted: int, skipped: int, resurfaced: int, unassigned: int, awaiting_account: int, needs_review: int, missing_dates: int, missing_source_fields: int, failed: int, partial: int} */
    private function syncUnlocked(?int $actorId, ?int $branchId, bool $refreshKnownSourceData = false): array
    {
        $sources = DB::table('branches')
            ->whereNotNull('google_spreadsheet_id')
            ->when($branchId, fn ($query) => $query->where('id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name', 'google_spreadsheet_id']);
        if ($sources->isEmpty()) {
            throw new RuntimeException($branchId ? 'Cabang ini belum memiliki Spreadsheet ID.' : 'Belum ada Spreadsheet ID pada konfigurasi cabang.');
        }

        try {
            $client = $this->googleClient();
        } catch (Throwable $exception) {
            $message = $this->failureMessage($exception);
            $this->recordFailures($this->sourceIds($sources), $message);
            $this->record('failed', null, $message);

            throw new RuntimeException($message, previous: $exception);
        }

        $summary = ['enriched' => 0, 'inserted' => 0, 'skipped' => 0, 'resurfaced' => 0, 'unassigned' => 0, 'awaiting_account' => 0, 'needs_review' => 0, 'missing_dates' => 0, 'missing_source_fields' => 0, 'failed' => 0, 'partial' => 0];
        $failures = [];
        foreach ($sources as $source) {
            try {
                $sourceData = $this->readBranch($client, (string) $source->google_spreadsheet_id);
                $sourceSummary = DB::transaction(function () use ($actorId, $source, $sourceData, $refreshKnownSourceData): array {
                    $importSummary = $this->leadImporter->import(
                        $sourceData['rows'],
                        $actorId,
                        (string) $source->google_spreadsheet_id,
                        (int) $source->id,
                        $refreshKnownSourceData,
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
                foreach (['enriched', 'inserted', 'skipped', 'resurfaced', 'unassigned', 'awaiting_account', 'needs_review', 'missing_dates', 'missing_source_fields'] as $key) {
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
        $metadata = $client->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}?fields=sheets.properties,properties.title")->throw()->json();
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
        $spreadsheetTitle = is_string($metadata['properties']['title'] ?? null) ? $metadata['properties']['title'] : '';
        foreach ($parsed['rows'] as &$row) {
            $row['source_date_raw'] = LeadSourceDate::fromDailyTab((string) $row['source_tab'], $spreadsheetTitle);
        }
        unset($row);
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

    private function googleClient(): PendingRequest
    {
        $credentialsPath = (string) config('leads.google_sheets.credentials_path');
        if ($credentialsPath === '' || ! is_readable($credentialsPath)) {
            throw new RuntimeException('File kredensial Google Sheets belum dikonfigurasi di server.');
        }

        return Http::withToken($this->accessToken($credentialsPath))->acceptJson()->timeout(25)->retry(3, 500);
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

    private function recordSourceEvent(int $branchId, ?int $actorId, string $action, ?string $previousSpreadsheetId, ?string $spreadsheetId, ?string $detail = null): void
    {
        DB::table('branch_source_events')->insert([
            'branch_id' => $branchId,
            'actor_id' => $actorId,
            'action' => $action,
            'previous_spreadsheet_id' => $previousSpreadsheetId,
            'spreadsheet_id' => $spreadsheetId,
            'detail' => $detail,
            'created_at' => now(),
        ]);
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
