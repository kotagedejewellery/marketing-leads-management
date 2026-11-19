<?php

namespace App\Support;

use RuntimeException;

final class DailyLeadTabParser
{
    /** @var array<string, string> */
    private const FIELDS = [
        'no' => 'source_sequence',
        'nama/nomor' => 'contact_raw',
        'respond time' => 'respond_time',
        'nama cs' => 'imported_cs_name',
        'keterangan' => 'imported_keterangan',
        'kebutuhan' => 'raw_kebutuhan',
        'greeting' => 'greeting',
        'waktu chat' => 'chat_time',
        'keterangan sumber' => 'source_description',
        'keterangan campaign' => 'campaign_description',
        'definisi leads' => 'imported_definition',
    ];

    /** @var list<string> */
    private const SOURCE_FIELDS = [
        'respond_time',
        'imported_cs_name',
        'imported_keterangan',
        'raw_kebutuhan',
        'greeting',
        'chat_time',
        'source_description',
        'campaign_description',
        'imported_definition',
    ];

    /**
     * @param  array<int, mixed>  $values
     * @return array{rows: list<array<string, string|null>>, missing_source_fields: int}
     */
    public function parse(string $tab, array $values): array
    {
        $sourceDateRow = $values[1] ?? [];
        $sourceDate = $this->value(is_array($sourceDateRow) ? ($sourceDateRow[1] ?? null) : null);
        $headers = [];
        $block = null;
        $rows = [];
        $missingSourceFields = 0;

        foreach ($values as $index => $valueRow) {
            $cells = is_array($valueRow) ? $valueRow : [];
            $dailyValues = array_slice($cells, 15, 11);
            $candidateHeaders = $this->headers($cells);
            if (isset($candidateHeaders[1]) && $candidateHeaders[1] === 'contact_raw') {
                $this->ensureCompleteHeaders($candidateHeaders, $tab);
                $headers = $candidateHeaders;

                continue;
            }
            $firstCell = $this->value($dailyValues[0] ?? null);
            if ($firstCell !== null && ! ctype_digit($firstCell) && $this->value($dailyValues[1] ?? null) === null) {
                $block = $firstCell;

                continue;
            }
            if ($headers === []) {
                continue;
            }
            $contact = $this->value($dailyValues[1] ?? null);
            if ($contact === null) {
                continue;
            }

            $row = array_fill_keys([
                'source_row_no', 'source_date_raw', 'source_tab', 'contact_raw', 'respond_time', 'imported_cs_name', 'imported_keterangan', 'raw_kebutuhan', 'greeting', 'chat_time', 'source_description', 'campaign_description', 'imported_definition',
            ], null);
            $sequence = null;
            foreach ($headers as $column => $field) {
                $value = $this->value($dailyValues[$column] ?? null);
                if ($field === 'source_sequence') {
                    $sequence = $value;

                    continue;
                }
                $row[$field] = $value;
            }
            $row['source_row_no'] = $this->sourceRowNumber($block, $sequence, $index + 1);
            $row['source_date_raw'] = $sourceDate;
            $row['source_tab'] = $tab;
            foreach (self::SOURCE_FIELDS as $field) {
                $missingSourceFields += $row[$field] === null ? 1 : 0;
            }
            $rows[] = $row;
        }
        if ($headers === []) {
            throw new RuntimeException("Struktur lead utama pada tab {$tab} belum sesuai.");
        }

        return ['rows' => $rows, 'missing_source_fields' => $missingSourceFields];
    }

    /** @param array<int, mixed> $headerRow
     *  @return array<int, string> */
    private function headers(array $headerRow): array
    {
        $headers = [];
        foreach (array_slice($headerRow, 15, 11) as $column => $header) {
            $key = mb_strtolower(trim(str_replace('*', '', (string) $header)));
            if (isset(self::FIELDS[$key])) {
                $headers[$column] = self::FIELDS[$key];
            }
        }

        return $headers;
    }

    /** @param array<int, string> $headers */
    private function ensureCompleteHeaders(array $headers, string $tab): void
    {
        if (array_diff(array_values(self::FIELDS), array_values($headers)) !== []) {
            throw new RuntimeException("Header lead utama pada tab {$tab} belum lengkap.");
        }
    }

    private function sourceRowNumber(?string $block, ?string $sequence, int $rowNumber): string
    {
        $prefix = $block ?? 'Baris';

        return $sequence === null ? "{$prefix} @{$rowNumber}" : "{$prefix} #{$sequence}";
    }

    private function value(mixed $value): ?string
    {
        $value = trim((string) $value);

        return in_array($value, ['', '-', '#N/A', '#REF!', '#VALUE!'], true) ? null : $value;
    }
}
