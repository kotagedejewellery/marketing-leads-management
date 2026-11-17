<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class GoogleSheetSyncService
{
    private const ROW_LIMIT = 5000;

    public function __construct(private readonly LeadImportService $leadImporter) {}

    /** @return array{inserted: int, skipped: int, resurfaced: int, unassigned: int, needs_review: int} */
    public function sync(?int $actorId = null): array
    {
        $spreadsheetId = (string) config('leads.google_sheets.spreadsheet_id');
        $credentialsPath = (string) config('leads.google_sheets.credentials_path');
        if ($spreadsheetId === '' || $credentialsPath === '' || ! is_readable($credentialsPath)) {
            throw new RuntimeException('Google Sheets belum dikonfigurasi. Periksa Spreadsheet ID dan file kredensial di server.');
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
            $metadata = $client->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}?fields=sheets.properties")->throw()->json();
            $sheets = is_array($metadata) && is_array($metadata['sheets'] ?? null)
                ? $metadata['sheets']
                : [];
            $tabs = collect($sheets)
                ->map(fn (mixed $sheet) => $sheet['properties']['title'] ?? null)
                ->filter(fn (mixed $title) => is_string($title) && preg_match('/^\d{1,2}$/', $title) === 1)
                ->sortBy(fn (string $title) => (int) $title)
                ->values();
            if ($tabs->isEmpty()) {
                throw new RuntimeException('Tidak menemukan tab harian bernama angka, misalnya 1 sampai 31.');
            }

            $rows = [];
            foreach ($tabs as $tab) {
                $range = rawurlencode("{$tab}!A1:Y".self::ROW_LIMIT);
                $values = $client->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}")->throw()->json('values', []);
                $this->appendTabRows($rows, is_array($values) ? $values : [], $tab);
            }
            $summary = $this->leadImporter->import($rows, $actorId, $spreadsheetId);
            $this->record('success', $summary);

            return $summary;
        } catch (Throwable $exception) {
            $this->record('failed', null, $exception->getMessage());
            throw $exception instanceof RuntimeException
                ? $exception
                : new RuntimeException('Sinkronisasi Google Sheets gagal. Coba lagi atau periksa koneksi dan akses Viewer.', previous: $exception);
        }
    }

    /** @param list<array<string, string|null>> $rows
     *  @param array<int, mixed> $values */
    private function appendTabRows(array &$rows, array $values, string $tab): void
    {
        $sourceDate = (string) (($values[1][1] ?? '') ?: '');
        $headers = $this->headers($values[3] ?? []);
        if (! in_array('contact_raw', $headers, true)) {
            throw new RuntimeException("Struktur lead utama pada tab {$tab} belum sesuai.");
        }
        foreach (array_slice($values, 4) as $index => $valueRow) {
            if (count($rows) >= self::ROW_LIMIT) {
                throw new RuntimeException('Maksimal 5.000 lead total dari seluruh tab harian.');
            }
            $cells = is_array($valueRow) ? $valueRow : [];
            $dailyValues = array_slice($cells, 15, 10);
            $contact = trim((string) ($dailyValues[1] ?? ''));
            if ($contact === '') {
                continue;
            }
            $row = array_fill_keys([
                'source_row_no', 'source_date_raw', 'source_tab', 'contact_raw', 'respond_time', 'imported_cs_name', 'imported_keterangan', 'raw_kebutuhan', 'greeting', 'chat_time', 'source_description', 'campaign_description', 'imported_definition',
            ], null);
            foreach ($headers as $column => $field) {
                $row[$field] = trim((string) ($dailyValues[$column] ?? '')) ?: null;
            }
            $row['source_row_no'] ??= (string) ($index + 5);
            $row['source_date_raw'] = $sourceDate ?: null;
            $row['source_tab'] = $tab;
            $rows[] = $row;
        }
    }

    /**
     *  @return array<int, string> */
    private function headers(mixed $headerRow): array
    {
        $fields = [
            'no' => 'source_row_no', 'nama/nomor' => 'contact_raw', 'respond time' => 'respond_time',
            'nama cs' => 'imported_cs_name', 'keterangan' => 'imported_keterangan', 'kebutuhan' => 'raw_kebutuhan',
            'greeting' => 'greeting', 'waktu chat' => 'chat_time', 'keterangan sumber' => 'source_description',
            'keterangan campaign' => 'campaign_description', 'definisi leads' => 'imported_definition',
        ];
        $headers = [];
        foreach (array_slice(is_array($headerRow) ? $headerRow : [], 15, 10) as $column => $header) {
            $key = mb_strtolower(trim(str_replace('*', '', (string) $header)));
            if (isset($fields[$key])) {
                $headers[$column] = $fields[$key];
            }
        }

        return $headers;
    }

    /** @param array{inserted: int, skipped: int, resurfaced: int, unassigned: int, needs_review: int}|null $summary */
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
