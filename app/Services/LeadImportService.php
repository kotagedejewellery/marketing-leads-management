<?php

namespace App\Services;

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
     * @return array{inserted: int, skipped: int, resurfaced: int, unassigned: int, needs_review: int}
     */
    public function import(array $rows, ?int $actorId, ?string $spreadsheetId = null, ?int $sourceBranchId = null): array
    {
        $known = $this->knownContacts($rows);
        $sourceBranch = $sourceBranchId
            ? DB::table('branches')->where('id', $sourceBranchId)->first(['id', 'name'])
            : null;
        $branchOwner = $sourceBranchId
            ? DB::table('users')->where('branch_id', $sourceBranchId)->where('role', 'cs')->where('active', true)->first(['id', 'name'])
            : null;
        if ($sourceBranchId && (! $sourceBranch || ! $branchOwner)) {
            throw new \RuntimeException('Cabang sumber harus memiliki satu akun CS aktif sebelum lead dapat disinkronkan.');
        }
        $csByName = DB::table('users')->where('role', 'cs')->where('active', true)->get(['id', 'name'])
            ->groupBy(fn (object $user) => $this->csKey($user->name))
            ->map(fn ($users) => $users->count() === 1 ? $users->first()->id : null)
            ->all();
        $summary = ['inserted' => 0, 'skipped' => 0, 'resurfaced' => 0, 'unassigned' => 0, 'needs_review' => 0];

        DB::transaction(function () use ($rows, $actorId, $spreadsheetId, $sourceBranchId, $sourceBranch, $branchOwner, $known, $csByName, &$summary): void {
            foreach ($rows as $row) {
                $contact = trim((string) ($row['contact_raw'] ?? ''));
                if ($contact === '') {
                    continue;
                }

                $key = $this->contactKey($contact);
                $sourceHash = $spreadsheetId ? $this->sourceHash($spreadsheetId, $row, $key) : null;
                if ($sourceHash && DB::table('lead_source_occurrences')->where('source_hash', $sourceHash)->exists()) {
                    $summary['skipped']++;

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
                        DB::table('lead_source_occurrences')->insert([
                            'lead_id' => $leadId,
                            'spreadsheet_id' => $spreadsheetId,
                            'source_tab' => $row['source_tab'] ?? '',
                            'source_row_no' => $row['source_row_no'] ?? null,
                            'source_date_raw' => $row['source_date_raw'] ?? null,
                            'source_hash' => $sourceHash,
                            'created_at' => now(),
                        ]);
                        $isCrossBranch = $sourceBranchId
                            && $knownLead['source_branch_id']
                            && $knownLead['source_branch_id'] !== $sourceBranchId;
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
                            'action' => $isCrossBranch ? 'Konflik sumber cabang' : 'Lead muncul kembali dari Google Sheets',
                            'detail' => $isCrossBranch
                                ? "Nomor ini sudah berasal dari {$knownLead['source_branch_name']}; kemunculan dari {$sourceBranch->name} tidak memindahkan CS pemilik."
                                : "Sumber: tab {$row['source_tab']} ({$row['source_date_raw']}). Data kerja CS tidak diubah.",
                            'created_at' => now(),
                        ]);
                        $summary['resurfaced']++;
                        $summary['needs_review'] += $isCrossBranch ? 1 : 0;
                    } else {
                        $summary['skipped']++;
                    }

                    continue;
                }

                preg_match('/^(\+?[0-9][0-9\s-]*)(?:\s+(.+))?$/u', $contact, $match);
                $owner = $branchOwner
                    ? (int) $branchOwner->id
                    : ($csByName[$this->csKey((string) ($row['imported_cs_name'] ?? ''))] ?? null);
                $milestones = $this->initialMilestones($row);
                $classification = $this->classification($row, $milestones);
                $leadId = DB::table('leads')->insertGetId([
                    'assigned_user_id' => $owner,
                    'source_branch_id' => $sourceBranchId,
                    'import_fingerprint' => hash('sha256', json_encode($row, JSON_THROW_ON_ERROR)),
                    'contact_key' => $key,
                    'source_row_no' => $row['source_row_no'] ?? null,
                    'source_date_raw' => $row['source_date_raw'] ?? null,
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
                    'detail' => $row['source_tab']
                        ? "Sumber: tab {$row['source_tab']} ({$row['source_date_raw']}). ".($sourceBranch ? "Ditugaskan otomatis ke {$branchOwner->name} ({$sourceBranch->name})." : ($owner ? 'CS terhubung dari nama pada sumber.' : 'Belum ditugaskan ke CS.'))
                        : ($owner ? 'CS terhubung dari nama pada CSV/XLSX.' : 'Belum ditugaskan ke CS.'),
                    'created_at' => now(),
                ]);
                if ($spreadsheetId && $sourceHash) {
                    DB::table('lead_source_occurrences')->insert([
                        'lead_id' => $leadId,
                        'spreadsheet_id' => $spreadsheetId,
                        'source_tab' => $row['source_tab'] ?? '',
                        'source_row_no' => $row['source_row_no'] ?? null,
                        'source_date_raw' => $row['source_date_raw'] ?? null,
                        'source_hash' => $sourceHash,
                        'created_at' => now(),
                    ]);
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
                $summary['needs_review'] += $classification ? 0 : 1;
            }
        });

        return $summary;
    }

    /** @param list<array<string, string|null>> $rows
     *  @return array<string, array{id: int, source_branch_id: int|null, source_branch_name: string|null}> */
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
            ->get(['l.id', 'l.contact_key', 'l.source_branch_id', 'b.name as source_branch_name']) as $lead) {
            $known[(string) $lead->contact_key] = [
                'id' => (int) $lead->id,
                'source_branch_id' => $lead->source_branch_id === null ? null : (int) $lead->source_branch_id,
                'source_branch_name' => $lead->source_branch_name,
            ];
        }

        return $known;
    }

    /** @param array<string, string|null> $row */
    private function sourceHash(string $spreadsheetId, array $row, string $contactKey): string
    {
        return hash('sha256', implode('|', [$spreadsheetId, $row['source_tab'] ?? '', $row['source_row_no'] ?? '', $contactKey]));
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

    private function csKey(string $name): string
    {
        return match (mb_strtolower(trim($name))) {
            'bela' => 'bella',
            default => mb_strtolower(trim($name)),
        };
    }
}
