<?php

namespace App\Services;

use App\Support\DailyLeadTabParser;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class GoogleSheetSyncService
{
    private const ROW_LIMIT = 5000;

    public function __construct(
        private readonly LeadImportService $leadImporter,
        private readonly DailyLeadTabParser $dailyLeadTabParser,
    ) {}

    /** @return array{enriched: int, inserted: int, skipped: int, resurfaced: int, unassigned: int, needs_review: int, missing_dates: int, missing_source_fields: int, failed: int} */
    public function sync(?int $actorId = null, ?int $branchId = null): array
    {
        $credentialsPath = (string) config('leads.google_sheets.credentials_path');
        if ($credentialsPath === '' || ! is_readable($credentialsPath)) {
            throw new RuntimeException('File kredensial Google Sheets belum dikonfigurasi di server.');
        }
        $sources = DB::table('branches')
            ->whereNotNull('google_spreadsheet_id')
            ->when($branchId, fn ($query) => $query->where('id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name', 'google_spreadsheet_id']);
        if ($sources->isEmpty()) {
            throw new RuntimeException($branchId ? 'Cabang ini belum memiliki Spreadsheet ID.' : 'Belum ada Spreadsheet ID pada konfigurasi cabang.');
        }

        try {
            $token = (new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/spreadsheets.readonly'],
                $credentialsPath,
            ))->fetchAuthToken();
            $accessToken = $token['access_token'] ?? null;
            if (! is_string($accessToken) || $accessToken === '') {
                throw new RuntimeException('Token Google Sheets tidak dapat dibuat.');
            }
            $client = Http::withToken($accessToken)->acceptJson()->timeout(25);
            $summary = ['enriched' => 0, 'inserted' => 0, 'skipped' => 0, 'resurfaced' => 0, 'unassigned' => 0, 'needs_review' => 0, 'missing_dates' => 0, 'missing_source_fields' => 0, 'failed' => 0];
            foreach ($sources as $source) {
                try {
                    $sourceSummary = $this->syncBranch($client, (int) $source->id, (string) $source->google_spreadsheet_id, $actorId);
                    foreach (['enriched', 'inserted', 'skipped', 'resurfaced', 'unassigned', 'needs_review', 'missing_dates', 'missing_source_fields'] as $key) {
                        $summary[$key] += $sourceSummary[$key];
                    }
                    DB::table('branches')->where('id', $source->id)->update(['google_sheets_synced_at' => now(), 'google_sheets_sync_error' => null]);
                } catch (Throwable $exception) {
                    DB::table('branches')->where('id', $source->id)->update(['google_sheets_sync_error' => $exception->getMessage()]);
                    $summary['failed']++;
                }
            }
            if ($summary['failed'] === $sources->count()) {
                throw new RuntimeException('Tidak ada cabang yang berhasil disinkronkan. Periksa konfigurasi sumber dan akun CS.');
            }
            $this->record($summary['failed'] ? 'partial' : 'success', $summary);

            return $summary;
        } catch (Throwable $exception) {
            $this->record('failed', null, $exception->getMessage());
            throw $exception instanceof RuntimeException
                ? $exception
                : new RuntimeException('Sinkronisasi Google Sheets gagal. Coba lagi atau periksa koneksi dan akses Viewer.', previous: $exception);
        }
    }

    /** @return array{enriched: int, inserted: int, skipped: int, resurfaced: int, unassigned: int, needs_review: int, missing_dates: int, missing_source_fields: int} */
    private function syncBranch(PendingRequest $client, int $branchId, string $spreadsheetId, ?int $actorId): array
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
        $ranges = array_map(fn (string $tab) => "{$tab}!A1:Z".self::ROW_LIMIT, $tabs);
        $query = implode('&', array_map(fn (string $range) => 'ranges='.rawurlencode($range), $ranges));
        $response = $client->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values:batchGet?{$query}")->throw()->json('valueRanges', []);
        $valueRanges = is_array($response) ? $response : [];
        $rows = [];
        $missingSourceFields = 0;
        foreach ($tabs as $index => $tab) {
            $parsed = $this->dailyLeadTabParser->parse(
                $tab,
                is_array($valueRanges[$index]['values'] ?? null) ? $valueRanges[$index]['values'] : [],
            );
            $rows = [...$rows, ...$parsed['rows']];
            $missingSourceFields += $parsed['missing_source_fields'];
            if (count($rows) > self::ROW_LIMIT) {
                throw new RuntimeException('Maksimal 5.000 lead total dari seluruh tab harian.');
            }
        }

        return [...$this->leadImporter->import($rows, $actorId, $spreadsheetId, $branchId), 'missing_source_fields' => $missingSourceFields];
    }

    /** @param array{enriched: int, inserted: int, skipped: int, resurfaced: int, unassigned: int, needs_review: int, missing_dates: int, missing_source_fields: int, failed: int}|null $summary */
    private function record(string $status, ?array $summary = null, ?string $error = null): void
    {
        DB::table('lead_sync_runs')->insert([
            'source' => 'google_sheets',
            'status' => $status,
            'summary' => $summary ? json_encode($summary, JSON_THROW_ON_ERROR) : null,
            'error' => $error,
            'created_at' => now(),
            'completed_at' => now(),
        ]);
    }
}
