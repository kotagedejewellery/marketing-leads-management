<?php

namespace App\Services;

use App\Support\CsName;
use App\Support\LeadSourceDate;
use Illuminate\Support\Facades\DB;

class LeadImportService
{
    private const MILESTONES = [
        'clicked' => 'Klik saja',
        'asked' => 'Tanya-Tanya',
        'price_list' => 'Sampai Pricelist',
        'offline' => 'Datang Offline',
        'online' => 'Diskusi Online',
        'closing_repeat' => 'Closing / Repeat Order',
    ];

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{enriched: int, inserted: int, skipped: int, resurfaced: int, unassigned: int, awaiting_account: int, needs_review: int, missing_dates: int}
     */
    public function import(array $rows, ?int $actorId, ?string $spreadsheetId = null, ?int $sourceBranchId = null, bool $refreshKnownSourceData = false): array
    {
        $known = $this->knownContacts($rows);
        $sourceBranch = $sourceBranchId
            ? DB::table('branches as b')
                ->leftJoin('users as u', fn ($join) => $join->on('u.id', '=', 'b.default_import_user_id')
                    ->where('u.branch_id', '=', $sourceBranchId)
                    ->where('u.role', 'cs')
                    ->where('u.active', true))
                ->where('b.id', $sourceBranchId)
                ->first(['b.id', 'b.name', 'u.id as default_import_user_id', 'u.name as default_import_name'])
            : null;
        $branchOwner = $sourceBranch?->default_import_user_id
            ? (object) ['id' => $sourceBranch->default_import_user_id, 'name' => $sourceBranch->default_import_name]
            : null;
        if ($sourceBranchId && ! $sourceBranch) {
            throw new \RuntimeException('Cabang sumber tidak ditemukan.');
        }
        if ($sourceBranchId && ! $branchOwner && collect($rows)->contains(
            fn (array $row): bool => trim((string) ($row['imported_cs_name'] ?? '')) === '',
        )) {
            throw new \RuntimeException('Cabang sumber harus memiliki CS fallback impor yang aktif sebelum lead dapat disinkronkan.');
        }
        $activeCs = DB::table('users')->where('role', 'cs')->where('active', true)
            ->when($sourceBranchId, fn ($query) => $query->where('branch_id', $sourceBranchId))
            ->get(['id', 'name'])
            ->map(fn (object $user): array => ['user_id' => (int) $user->id, 'key' => CsName::key($user->name)]);
        $sourceNames = $sourceBranchId
            ? $activeCs->concat(DB::table('cs_source_name_aliases as alias')
                ->join('users as user', 'user.id', '=', 'alias.user_id')
                ->where('alias.branch_id', $sourceBranchId)
                ->where('user.role', 'cs')
                ->where('user.active', true)
                ->where('user.branch_id', $sourceBranchId)
                ->get(['alias.user_id', 'alias.source_name_key'])
                ->map(fn (object $alias): array => ['user_id' => (int) $alias->user_id, 'key' => CsName::key($alias->source_name_key)]))
            : $activeCs;
        $csByName = $sourceNames
            ->groupBy('key')
            ->map(function ($identities): ?int {
                $userIds = $identities->pluck('user_id')->unique()->values();

                return $userIds->count() === 1 ? (int) $userIds->first() : null;
            })
            ->all();
        $summary = ['enriched' => 0, 'inserted' => 0, 'skipped' => 0, 'resurfaced' => 0, 'unassigned' => 0, 'awaiting_account' => 0, 'needs_review' => 0, 'missing_dates' => 0];

        DB::transaction(function () use ($rows, $actorId, $spreadsheetId, $sourceBranchId, $sourceBranch, $branchOwner, $known, $csByName, $refreshKnownSourceData, &$summary): void {
            foreach ($rows as $row) {
                $contact = trim((string) ($row['contact_raw'] ?? ''));
                if ($contact === '') {
                    continue;
                }

                $key = $this->contactKey($contact);
                $sourceHash = $spreadsheetId ? $this->sourceHash($spreadsheetId, $row, $key) : null;
                $sourceOccurrenceLeadId = $sourceHash
                    ? DB::table('lead_source_occurrences')->where('source_hash', $sourceHash)->value('lead_id')
                    : null;
                if ($sourceOccurrenceLeadId !== null) {
                    $sourceOccurrenceLeadId = (int) $sourceOccurrenceLeadId;
                }
                if ($sourceOccurrenceLeadId === null && $spreadsheetId && $sourceHash && ($row['source_tab'] ?? null)) {
                    $sourceOccurrenceLeadId = $this->sourceOccurrenceLeadIdForMovedRow($spreadsheetId, $row, $contact);
                    if ($sourceOccurrenceLeadId !== null) {
                        $this->recordSourceOccurrence($sourceOccurrenceLeadId, $spreadsheetId, $sourceBranchId, $row, $sourceHash);
                    }
                }
                if ($sourceOccurrenceLeadId !== null) {
                    if ($this->refreshSourceData((int) $sourceOccurrenceLeadId, $row)) {
                        $summary['enriched']++;
                        DB::table('lead_activity')->insert([
                            'lead_id' => $sourceOccurrenceLeadId,
                            'actor_id' => $actorId,
                            'action' => 'Data sumber diperbarui dari Google Sheets',
                            'detail' => "Sumber: tab {$row['source_tab']} ({$row['source_date_raw']}). Data kerja CS tidak diubah.",
                            'created_at' => now(),
                        ]);
                    } else {
                        $summary['skipped']++;
                    }

                    continue;
                }

                $knownLead = null;
                foreach ($this->contactKeys($contact) as $candidate) {
                    if (isset($known[$candidate])) {
                        $knownLead = $known[$candidate];

                        break;
                    }
                }
                if ($knownLead) {
                    $leadId = $knownLead['id'];
                    if ($spreadsheetId && $sourceHash) {
                        $isCrossBranch = $sourceBranchId
                            && $knownLead['source_branch_id']
                            && $knownLead['source_branch_id'] !== $sourceBranchId;
                        $sourceDataUpdated = ! $isCrossBranch
                            && $refreshKnownSourceData
                            && $this->refreshSourceData($leadId, $row);
                        $this->recordSourceOccurrence($leadId, $spreadsheetId, $sourceBranchId, $row, $sourceHash);
                        if ($isCrossBranch) {
                            DB::table('leads')->where('id', $leadId)->update([
                                'source_conflict_at' => now(),
                                'source_conflict_branch_id' => $sourceBranchId,
                                'updated_at' => now(),
                            ]);
                        }
                        DB::table('lead_activity')->insert([
                            'lead_id' => $leadId,
                            'actor_id' => $actorId,
                            'action' => $isCrossBranch
                                ? 'Konflik sumber cabang'
                                : ($sourceDataUpdated ? 'Data sumber diperbarui dari spreadsheet pengganti' : 'Lead muncul kembali dari Google Sheets'),
                            'detail' => $isCrossBranch
                                ? "Nomor ini sudah berasal dari {$knownLead['source_branch_name']}; kemunculan dari {$sourceBranch->name} tidak memindahkan CS pemilik."
                                : "Sumber: tab {$row['source_tab']} ({$row['source_date_raw']}). Data kerja CS tidak diubah.",
                            'created_at' => now(),
                        ]);
                        $summary[$sourceDataUpdated ? 'enriched' : 'resurfaced']++;
                        $summary['needs_review'] += $isCrossBranch ? 1 : 0;
                    } else {
                        $summary['skipped']++;
                    }

                    continue;
                }

                preg_match('/^(\+?[0-9][0-9\s-]*)(?:\s+(.+))?$/u', $contact, $match);
                $sourceCsName = trim((string) ($row['imported_cs_name'] ?? ''));
                [$owner, $ownerResolution] = $this->resolveOwner(
                    $sourceCsName,
                    $sourceBranchId,
                    $branchOwner,
                    $csByName,
                );
                $milestones = $this->initialMilestones($row);
                $classification = $this->classification($row, $milestones);
                $leadCreatedOn = LeadSourceDate::parse($row['source_date_raw'] ?? null);
                $leadId = DB::table('leads')->insertGetId([
                    'assigned_user_id' => $owner,
                    'owner_resolution' => $ownerResolution,
                    'source_branch_id' => $sourceBranchId,
                    'import_fingerprint' => hash('sha256', json_encode($row, JSON_THROW_ON_ERROR)),
                    'contact_key' => $key,
                    'source_row_no' => $row['source_row_no'] ?? null,
                    'source_date_raw' => $row['source_date_raw'] ?? null,
                    'lead_created_on' => $leadCreatedOn,
                    'contact_raw' => $contact,
                    'name' => $match[2] ?? null,
                    'phone' => $match[1] ?? null,
                    'respond_time' => $row['respond_time'] ?? null,
                    'imported_cs_name' => $row['imported_cs_name'] ?? null,
                    'imported_keterangan' => $row['imported_keterangan'] ?? null,
                    'raw_kebutuhan' => $row['raw_kebutuhan'] ?? null,
                    'greeting' => $row['greeting'] ?? null,
                    'chat_time' => $row['chat_time'] ?? null,
                    'source_description' => $row['source_description'] ?? null,
                    'campaign_description' => $row['campaign_description'] ?? null,
                    'imported_definition' => $classification,
                    'current_stage' => $this->currentStage($milestones),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                foreach ($milestones as $milestone) {
                    DB::table('lead_milestones')->insert([
                        'lead_id' => $leadId,
                        'milestone' => $milestone,
                        'is_active' => true,
                        'marked_at' => now(),
                        'marked_by' => $actorId,
                    ]);
                }
                DB::table('lead_activity')->insert([
                    'lead_id' => $leadId,
                    'actor_id' => $actorId,
                    'action' => $row['source_tab'] ? 'Diimpor dari tab harian' : 'Diimpor dari CSV/XLSX',
                    'detail' => $this->ownerImportDetail($row, $sourceBranch, $branchOwner, $sourceCsName, $owner, $ownerResolution),
                    'created_at' => now(),
                ]);
                if ($spreadsheetId && $sourceHash) {
                    $this->recordSourceOccurrence($leadId, $spreadsheetId, $sourceBranchId, $row, $sourceHash);
                }
                foreach ($this->contactKeys($contact) as $candidate) {
                    $known[$candidate] = [
                        'id' => $leadId,
                        'source_branch_id' => $sourceBranchId,
                        'source_branch_name' => $sourceBranch?->name,
                    ];
                }
                $summary['inserted']++;
                $summary['unassigned'] += $owner ? 0 : 1;
                $summary['awaiting_account'] += $ownerResolution === 'awaiting_account' ? 1 : 0;
                $summary['needs_review'] += $classification ? 0 : 1;
                $summary['missing_dates'] += $leadCreatedOn ? 0 : 1;
            }
        });

        return $summary;
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{rows: int, matching: int, new: int, updates: int, conflicts: int}
     */
    public function preview(array $rows, int $sourceBranchId): array
    {
        $known = $this->knownContacts($rows);
        $summary = ['rows' => 0, 'matching' => 0, 'new' => 0, 'updates' => 0, 'conflicts' => 0];

        foreach ($rows as $row) {
            $contact = trim((string) ($row['contact_raw'] ?? ''));
            if ($contact === '') {
                continue;
            }
            $summary['rows']++;

            $knownLead = null;
            foreach ($this->contactKeys($contact) as $candidate) {
                if (isset($known[$candidate])) {
                    $knownLead = $known[$candidate];

                    break;
                }
            }
            if (! $knownLead) {
                $summary['new']++;

                continue;
            }
            if ($knownLead['source_branch_id'] && $knownLead['source_branch_id'] !== $sourceBranchId) {
                $summary['conflicts']++;

                continue;
            }

            $summary['matching']++;
            $summary['updates'] += $this->sourceDataUpdates($knownLead, $row) === [] ? 0 : 1;
        }

        return $summary;
    }

    /** @param list<array<string, string|null>> $rows
     *  @return array<string, array<string, int|string|null>> */
    private function knownContacts(array $rows): array
    {
        $keys = [];
        foreach ($rows as $row) {
            foreach ($this->contactKeys((string) ($row['contact_raw'] ?? '')) as $key) {
                $keys[$key] = true;
            }
        }

        $known = [];
        foreach (DB::table('leads as l')
            ->leftJoin('branches as b', 'b.id', '=', 'l.source_branch_id')
            ->whereIn('l.contact_key', array_keys($keys))
            ->get([
                'l.id', 'l.contact_key', 'l.source_branch_id', 'b.name as source_branch_name',
                'l.source_date_raw', 'l.lead_created_on', 'l.respond_time', 'l.imported_cs_name',
                'l.imported_keterangan', 'l.raw_kebutuhan', 'l.greeting', 'l.chat_time',
                'l.source_description', 'l.campaign_description', 'l.imported_definition',
            ]) as $lead) {
            $known[(string) $lead->contact_key] = [
                'id' => (int) $lead->id,
                'source_branch_id' => $lead->source_branch_id === null ? null : (int) $lead->source_branch_id,
                'source_branch_name' => $lead->source_branch_name,
                'source_date_raw' => $lead->source_date_raw,
                'lead_created_on' => $lead->lead_created_on,
                'respond_time' => $lead->respond_time,
                'imported_cs_name' => $lead->imported_cs_name,
                'imported_keterangan' => $lead->imported_keterangan,
                'raw_kebutuhan' => $lead->raw_kebutuhan,
                'greeting' => $lead->greeting,
                'chat_time' => $lead->chat_time,
                'source_description' => $lead->source_description,
                'campaign_description' => $lead->campaign_description,
                'imported_definition' => $lead->imported_definition,
            ];
        }

        return $known;
    }

    /** @param array<string, string|null> $row */
    private function sourceHash(string $spreadsheetId, array $row, string $contactKey): string
    {
        return hash('sha256', implode('|', [$spreadsheetId, $row['source_tab'] ?? '', $row['source_row_no'] ?? '', $contactKey]));
    }

    /** @param array<string, string|null> $row */
    private function sourceOccurrenceLeadIdForMovedRow(string $spreadsheetId, array $row, string $contact): ?int
    {
        $leadId = DB::table('lead_source_occurrences as occurrence')
            ->join('leads as lead', 'lead.id', '=', 'occurrence.lead_id')
            ->where('occurrence.spreadsheet_id', $spreadsheetId)
            ->where('occurrence.source_tab', $row['source_tab'])
            ->whereIn('lead.contact_key', $this->contactKeys($contact))
            ->orderByDesc('occurrence.created_at')
            ->value('occurrence.lead_id');

        return $leadId === null ? null : (int) $leadId;
    }

    /** @param array<string, string|null> $row */
    private function recordSourceOccurrence(int $leadId, string $spreadsheetId, ?int $sourceBranchId, array $row, string $sourceHash): void
    {
        DB::table('lead_source_occurrences')->insertOrIgnore([
            'lead_id' => $leadId,
            'source_branch_id' => $sourceBranchId,
            'spreadsheet_id' => $spreadsheetId,
            'source_tab' => $row['source_tab'] ?? '',
            'source_row_no' => $row['source_row_no'] ?? null,
            'source_date_raw' => $row['source_date_raw'] ?? null,
            'source_hash' => $sourceHash,
            'created_at' => now(),
        ]);
    }

    /** @param array<string, string|null> $row */
    private function refreshSourceData(int $leadId, array $row): bool
    {
        $lead = (array) DB::table('leads')->where('id', $leadId)->first([
            'source_date_raw', 'lead_created_on', 'respond_time', 'imported_cs_name', 'imported_keterangan',
            'raw_kebutuhan', 'greeting', 'chat_time', 'source_description', 'campaign_description', 'imported_definition',
        ]);
        if (! $lead) {
            return false;
        }

        $updates = $this->sourceDataUpdates($lead, $row);
        if ($updates === []) {
            return false;
        }

        DB::table('leads')->where('id', $leadId)->update([...$updates, 'updated_at' => now()]);

        return true;
    }

    /** @param array<string, int|string|null> $lead
     * @param  array<string, string|null>  $row
     * @return array<string, string>
     */
    private function sourceDataUpdates(array $lead, array $row): array
    {
        $updates = [];
        foreach ([
            'source_date_raw', 'respond_time', 'imported_cs_name', 'imported_keterangan', 'raw_kebutuhan',
            'greeting', 'chat_time', 'source_description', 'campaign_description',
        ] as $field) {
            $value = $row[$field] ?? null;
            if ($value !== null && $value !== '' && ($lead[$field] ?? null) !== $value) {
                $updates[$field] = $value;
            }
        }
        $classification = $this->classification($row, $this->initialMilestones($row));
        if ($classification !== null && ($lead['imported_definition'] ?? null) !== $classification) {
            $updates['imported_definition'] = $classification;
        }
        $leadCreatedOn = LeadSourceDate::parse($row['source_date_raw'] ?? null);
        if ($leadCreatedOn !== null && (string) ($lead['lead_created_on'] ?? '') !== $leadCreatedOn) {
            $updates['lead_created_on'] = $leadCreatedOn;
        }

        return $updates;
    }

    private function contactKey(string $contact): string
    {
        $key = $this->legacyContactKey($contact);

        return str_starts_with($key, 'phone:0') ? 'phone:62'.substr($key, 7) : $key;
    }

    /** @return list<string> */
    private function contactKeys(string $contact): array
    {
        $normalized = $this->contactKey($contact);
        $keys = [$normalized, $this->legacyContactKey($contact)];
        if (str_starts_with($normalized, 'phone:62')) {
            $keys[] = 'phone:0'.substr($normalized, 8);
        }

        return array_values(array_unique($keys));
    }

    private function legacyContactKey(string $contact): string
    {
        $contact = trim($contact);
        $digits = preg_replace('/\D/', '', $contact) ?? '';

        return strlen($digits) >= 8 ? "phone:{$digits}" : 'contact:'.mb_strtolower($contact);
    }

    /** @param array<string, string|null> $row
     *  @return list<string> */
    private function initialMilestones(array $row): array
    {
        $text = mb_strtolower(trim(($row['imported_keterangan'] ?? '').' '.($row['imported_definition'] ?? '')));

        return match (true) {
            str_contains($text, 'repeat') || str_contains($text, 'closing') => ['closing_repeat'],
            str_contains($text, 'offline') || str_contains($text, 'datang') => ['offline'],
            str_contains($text, 'online') => ['online'],
            str_contains($text, 'pricelist') || str_contains($text, 'sampai pl') => ['price_list'],
            str_contains($text, 'tanya') || str_contains($text, 'warm') => ['asked'],
            str_contains($text, 'klik') || str_contains($text, 'cold') => ['clicked'],
            default => [],
        };
    }

    /** @param array<string, string|null> $row
     *  @param list<string> $milestones */
    private function classification(array $row, array $milestones): ?string
    {
        $definition = mb_strtolower(trim((string) ($row['imported_definition'] ?? '')));
        if (in_array($definition, ['cold', 'warm', 'hot'], true)) {
            return ucfirst($definition);
        }

        return match ($milestones[0] ?? null) {
            'clicked' => 'Cold',
            'asked', 'price_list' => 'Warm',
            'offline', 'online', 'closing_repeat' => 'Hot',
            default => null,
        };
    }

    /** @param list<string> $milestones */
    private function currentStage(array $milestones): ?string
    {
        foreach (array_reverse(array_keys(self::MILESTONES)) as $milestone) {
            if (in_array($milestone, $milestones, true)) {
                return $milestone;
            }
        }

        return null;
    }

    /**
     * @param  array<string, int|null>  $csByName
     * @return array{0: int|null, 1: 'manual'|'source_matched'|'fallback'|'awaiting_account'|'unassigned'}
     */
    private function resolveOwner(string $sourceCsName, ?int $sourceBranchId, ?object $branchOwner, array $csByName): array
    {
        if ($sourceCsName !== '') {
            $owner = $csByName[CsName::key($sourceCsName)] ?? null;

            return $owner ? [(int) $owner, 'source_matched'] : [null, $sourceBranchId ? 'awaiting_account' : 'unassigned'];
        }

        if ($sourceBranchId && $branchOwner) {
            return [(int) $branchOwner->id, 'fallback'];
        }

        return [null, 'unassigned'];
    }

    /** @param array<string, string|null> $row */
    private function ownerImportDetail(array $row, ?object $sourceBranch, ?object $branchOwner, string $sourceCsName, ?int $owner, string $ownerResolution): string
    {
        $source = $row['source_tab']
            ? "Sumber: tab {$row['source_tab']} ({$row['source_date_raw']}). "
            : '';

        return $source.match ($ownerResolution) {
            'source_matched' => 'Ditugaskan dari Nama CS sumber ke akun CS cabang yang aktif.',
            'fallback' => "Nama CS sumber kosong; ditugaskan sementara ke {$branchOwner->name} ({$sourceBranch->name}).",
            'awaiting_account' => "Nama CS sumber {$sourceCsName} belum memiliki akun CS aktif di {$sourceBranch->name}; menunggu akun CS.",
            default => $owner ? 'CS terhubung dari nama pada CSV/XLSX.' : 'Belum ditugaskan ke CS.',
        };
    }
}
