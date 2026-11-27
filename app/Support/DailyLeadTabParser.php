<?php

namespace App\Support;

use RuntimeException;

final class DailyLeadTabParser
{
    /** @var array<string, string> */
    private const FIELDS = [
        'no' => 'source_sequence',
        'nama nomor' => 'contact_raw',
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

    /** @var array<string, string> */
    private const FIELD_LABELS = [
        'source_sequence' => 'No',
        'contact_raw' => 'Nama/Nomor',
        'respond_time' => 'Respond Time',
        'imported_cs_name' => 'Nama CS',
        'imported_keterangan' => 'Keterangan',
        'raw_kebutuhan' => 'Kebutuhan',
        'greeting' => 'Greeting',
        'chat_time' => 'Waktu Chat',
        'source_description' => 'Keterangan Sumber',
        'campaign_description' => 'Keterangan Campaign',
        'imported_definition' => 'Definisi Leads',
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
        if ($this->isEmpty($values)) {
            return ['rows' => [], 'missing_source_fields' => 0];
        }

        $sourceDateRow = $values[1] ?? [];
        $sourceDate = $this->value(is_array($sourceDateRow) ? ($sourceDateRow[1] ?? null) : null);
        $headers = [];
        $block = null;
        $rows = [];
        $missingSourceFields = 0;

        foreach ($values as $index => $valueRow) {
            $cells = is_array($valueRow) ? $valueRow : [];
            $candidateHeaders = $this->headers($cells);
            if (in_array('contact_raw', $candidateHeaders, true)) {
                if ($headers === []) {
                    $this->ensureCompleteHeaders($candidateHeaders, $tab, $index + 1);
                    $headers = $candidateHeaders;
                    $sequenceColumn = array_search('source_sequence', $headers, true);
                    $previousRow = $values[$index - 1] ?? [];
                    $label = is_array($previousRow) && $sequenceColumn !== false
                        ? $this->value($previousRow[$sequenceColumn] ?? null)
                        : null;
                    if ($label !== null && ! ctype_digit($label)) {
                        $block = $label;
                    }
                } elseif (count($candidateHeaders) === count(self::FIELDS)) {
                    $headers = $candidateHeaders;
                }

                continue;
            }
            if ($headers === []) {
                $block = $this->singleValue($cells) ?? $block;

                continue;
            }
            $contactColumn = array_search('contact_raw', $headers, true);
            $sequenceColumn = array_search('source_sequence', $headers, true);
            if ($contactColumn === false || $sequenceColumn === false) {
                continue;
            }
            $firstCell = $this->value($cells[$sequenceColumn] ?? null);
            if ($firstCell !== null && ! ctype_digit($firstCell) && $this->value($cells[$contactColumn] ?? null) === null) {
                $block = $firstCell;

                continue;
            }
            $contact = $this->value($cells[$contactColumn] ?? null);
            if ($contact === null) {
                continue;
            }

            $row = array_fill_keys([
                'source_row_no', 'source_date_raw', 'source_tab', 'contact_raw', 'respond_time', 'imported_cs_name', 'imported_keterangan', 'raw_kebutuhan', 'greeting', 'chat_time', 'source_description', 'campaign_description', 'imported_definition',
            ], null);
            $sequence = null;
            foreach ($headers as $column => $field) {
                $value = $this->value($cells[$column] ?? null);
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

    /**
     * @param  array<string, array<int, mixed>>  $tabs
     * @return array{rows: list<array<string, string|null>>, missing_source_fields: int, errors: list<string>}
     */
    public function parseAll(array $tabs): array
    {
        $rows = [];
        $missingSourceFields = 0;
        $errors = [];

        foreach ($tabs as $tab => $values) {
            try {
                $parsed = $this->parse($tab, $values);
            } catch (RuntimeException $exception) {
                $errors[] = "Tab {$tab}: {$exception->getMessage()}";

                continue;
            }
            $rows = [...$rows, ...$parsed['rows']];
            $missingSourceFields += $parsed['missing_source_fields'];
        }

        return ['rows' => $rows, 'missing_source_fields' => $missingSourceFields, 'errors' => $errors];
    }

    /** @param array<int, mixed> $headerRow
     *  @return array<int, string> */
    private function headers(array $headerRow): array
    {
        $headers = [];
        foreach ($headerRow as $column => $header) {
            $key = $this->headerKey($header);
            if (isset(self::FIELDS[$key])) {
                if (in_array(self::FIELDS[$key], $headers, true)) {
                    break;
                }
                $headers[$column] = self::FIELDS[$key];
            }
        }

        return $headers;
    }

    /** @param array<int, string> $headers */
    private function ensureCompleteHeaders(array $headers, string $tab, int $row): void
    {
        $missing = array_values(array_diff(array_values(self::FIELDS), array_values($headers)));
        if ($missing !== []) {
            $labels = array_map(fn (string $field): string => self::FIELD_LABELS[$field], $missing);

            throw new RuntimeException("Header lead utama pada tab {$tab}, baris {$row} belum lengkap: ".implode(', ', $labels).'.');
        }
    }

    private function headerKey(mixed $header): string
    {
        $value = str_replace(["\u{00a0}", "\u{200b}", "\u{feff}", '*'], ' ', (string) $header);
        $value = preg_replace('/[^\\p{L}\\p{N}]+/u', ' ', $value) ?? '';

        return mb_strtolower(trim(preg_replace('/\\s+/u', ' ', $value) ?? ''));
    }

    /** @param array<int, mixed> $values */
    private function isEmpty(array $values): bool
    {
        foreach ($values as $row) {
            foreach (is_array($row) ? $row : [] as $cell) {
                if ($this->value($cell) !== null) {
                    return false;
                }
            }
        }

        return true;
    }

    /** @param array<int, mixed> $cells */
    private function singleValue(array $cells): ?string
    {
        $values = [];
        foreach ($cells as $cell) {
            $value = $this->value($cell);
            if ($value !== null) {
                $values[] = $value;
            }
        }

        return count($values) === 1 ? $values[0] : null;
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
