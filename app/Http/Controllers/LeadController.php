<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLeadRequest;
use DateTimeInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class LeadController extends Controller
{
    private const IMPORT_ROW_LIMIT = 5000;

    private const MILESTONES = [
        'clicked' => 'Klik saja',
        'asked' => 'Tanya-tanya saja',
        'price_list' => 'Sampai Pricelist',
        'offline' => 'Datang Offline',
        'online' => 'Diskusi Online',
        'closing_pending_transfer' => 'Closing tapi belum Transfer',
        'closing' => 'Closing',
        'repeat_order' => 'Repeat Order',
    ];

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $data = $request->validate([
            'contact_raw' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'source_date_raw' => ['nullable', 'string', 'max:60'],
            'respond_time' => ['nullable', 'string', 'max:60'],
            'imported_cs_name' => ['nullable', 'string', 'max:120'],
            'imported_keterangan' => ['nullable', 'string'],
            'raw_kebutuhan' => ['nullable', 'string'],
            'greeting' => ['nullable', 'string'],
            'chat_time' => ['nullable', 'string', 'max:30'],
            'source_description' => ['nullable', 'string', 'max:160'],
            'campaign_description' => ['nullable', 'string', 'max:160'],
            'intent_buying' => ['nullable', 'in:Tunangan,Lamaran,Nikahan,Anniversary,Lainnya'],
            'usage_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'string', 'max:120'],
            'keterangan' => ['nullable', 'string'],
            'next_follow_up_at' => ['nullable', 'date'],
            'milestone' => ['nullable', 'in:clicked,asked,price_list,offline,online,closing_pending_transfer,closing,repeat_order'],
        ]);
        $key = $this->contactKey(($data['phone'] ?? null) ?: $data['contact_raw']);
        if (DB::table('leads')->where('contact_key', $key)->exists()) {
            throw ValidationException::withMessages(['contact_raw' => 'Lead dengan kontak ini sudah ada.']);
        }
        if (($data['assigned_user_id'] ?? null) && ! DB::table('users')->where('id', $data['assigned_user_id'])->where('role', 'cs')->where('active', true)->exists()) {
            throw ValidationException::withMessages(['assigned_user_id' => 'CS aktif tidak valid.']);
        }

        $id = DB::transaction(function () use ($data, $key, $request): int {
            $milestone = $data['milestone'] ?? null;
            $id = DB::table('leads')->insertGetId([
                'contact_key' => $key,
                'contact_raw' => $data['contact_raw'],
                'name' => $data['name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'assigned_user_id' => $data['assigned_user_id'] ?? null,
                'source_date_raw' => $data['source_date_raw'] ?? null,
                'respond_time' => $data['respond_time'] ?? null,
                'imported_cs_name' => $data['imported_cs_name'] ?? null,
                'imported_keterangan' => $data['imported_keterangan'] ?? null,
                'raw_kebutuhan' => $data['raw_kebutuhan'] ?? null,
                'greeting' => $data['greeting'] ?? null,
                'chat_time' => $data['chat_time'] ?? null,
                'source_description' => $data['source_description'] ?? null,
                'campaign_description' => $data['campaign_description'] ?? null,
                'imported_definition' => $this->classificationForMilestone($milestone),
                'intent_buying' => $data['intent_buying'] ?? null,
                'usage_date' => $data['usage_date'] ?? null,
                'budget' => $data['budget'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'next_follow_up_at' => $data['next_follow_up_at'] ?? null,
                'current_stage' => $milestone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($milestone) {
                DB::table('lead_milestones')->insert(['lead_id' => $id, 'milestone' => $milestone, 'is_active' => true, 'marked_at' => now(), 'marked_by' => $request->user()->id]);
            }
            DB::table('lead_activity')->insert(['lead_id' => $id, 'actor_id' => $request->user()->id, 'action' => 'Lead dibuat manual', 'detail' => ($data['assigned_user_id'] ?? null) ? 'Lead langsung ditugaskan ke CS.' : 'Lead belum ditugaskan ke CS.', 'created_at' => now()]);

            return $id;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead ditambahkan.']);

        return to_route('leads.show', $id);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $request->validate(
            ['csv_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240']],
            ['csv_file.uploaded' => 'File gagal diunggah. Gunakan CSV/XLSX maksimal 10 MB.'],
        );

        $extension = strtolower($request->file('csv_file')->getClientOriginalExtension());
        [$headers, $valueRows] = $extension === 'xlsx'
            ? $this->readXlsx($request->file('csv_file')->getRealPath())
            : $this->readCsv($request->file('csv_file')->getRealPath());
        $headerMap = $this->importHeaderMap($headers);
        $required = ['contact_raw', 'respond_time', 'imported_cs_name', 'imported_keterangan', 'raw_kebutuhan', 'greeting', 'chat_time', 'source_description', 'campaign_description'];
        if (array_diff($required, array_values($headerMap)) || (! in_array('source_date_raw', $headerMap, true) && ! in_array('source_row_no', $headerMap, true))) {
            throw ValidationException::withMessages(['csv_file' => 'Header CSV belum sesuai template.']);
        }

        $rows = [];
        $line = 1;
        foreach ($valueRows as $values) {
            $line++;
            if (count($values) > count($headers)) {
                throw ValidationException::withMessages(['csv_file' => "Jumlah kolom CSV tidak sesuai pada baris {$line}."]);
            }
            $values = array_pad($values, count($headers), null);
            $row = array_combine($headers, $values);
            $normalized = $this->normalizeImportRow($row, $headerMap);
            if (trim((string) $normalized['contact_raw']) !== '') {
                $rows[] = $normalized;
            }
        }
        $keys = array_values(array_unique(array_map(fn ($row) => $this->contactKey($row['contact_raw']), $rows)));
        $existing = collect($keys)->chunk(500)->flatMap(fn ($chunk) => DB::table('leads')->whereIn('contact_key', $chunk)->pluck('contact_key'))->flip()->all();
        $csByName = DB::table('users')->where('role', 'cs')->where('active', true)->get(['id', 'name'])
            ->groupBy(fn ($user) => $this->csKey($user->name))
            ->map(fn ($users) => $users->count() === 1 ? $users->first()->id : null)
            ->all();
        $inserted = 0;
        $skipped = 0;
        $unassigned = 0;
        $needsReview = 0;

        DB::transaction(function () use ($rows, $request, $existing, $csByName, &$inserted, &$skipped, &$unassigned, &$needsReview): void {
            $known = $existing;

            foreach ($rows as $row) {
                $contact = trim((string) $row['contact_raw']);
                $key = $this->contactKey($contact);
                if (isset($known[$key])) {
                    $skipped++;

                    continue;
                }

                preg_match('/^(\+?[0-9][0-9\s-]*)(?:\s+(.+))?$/u', $contact, $match);
                $owner = $csByName[$this->csKey((string) $row['imported_cs_name'])] ?? null;
                $classification = $this->importClassification($row);
                $initialMilestones = $this->initialMilestones($row);
                $id = DB::table('leads')->insertGetId([
                    'assigned_user_id' => $owner,
                    'import_fingerprint' => hash('sha256', json_encode($row, JSON_THROW_ON_ERROR)),
                    'contact_key' => $key,
                    'source_row_no' => $row['source_row_no'],
                    'source_date_raw' => $row['source_date_raw'],
                    'contact_raw' => $contact,
                    'name' => $match[2] ?? null,
                    'phone' => $match[1] ?? null,
                    'respond_time' => $row['respond_time'],
                    'imported_cs_name' => $row['imported_cs_name'],
                    'imported_keterangan' => $row['imported_keterangan'],
                    'raw_kebutuhan' => $row['raw_kebutuhan'],
                    'greeting' => $row['greeting'],
                    'chat_time' => $row['chat_time'],
                    'source_description' => $row['source_description'],
                    'campaign_description' => $row['campaign_description'],
                    'imported_definition' => $classification,
                    'current_stage' => $this->currentStage($initialMilestones),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($initialMilestones as $milestone) {
                    DB::table('lead_milestones')->insert([
                        'lead_id' => $id,
                        'milestone' => $milestone,
                        'is_active' => true,
                        'marked_at' => now(),
                        'marked_by' => $request->user()->id,
                    ]);
                }

                DB::table('lead_activity')->insert([
                    'lead_id' => $id,
                    'actor_id' => $request->user()->id,
                    'action' => 'Diimpor dari CSV',
                    'detail' => $owner ? 'CS terhubung dari nama pada CSV.' : 'Belum ditugaskan ke CS.',
                    'created_at' => now(),
                ]);
                $known[$key] = true;
                $inserted++;
                $unassigned += $owner ? 0 : 1;
                $needsReview += $classification ? 0 : 1;
            }
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Import selesai: {$inserted} baru, {$skipped} duplikat dilewati, {$unassigned} perlu ditugaskan, {$needsReview} perlu review.",
        ]);

        return to_route('leads.index');
    }

    public function show(Request $request, int $lead): Response
    {
        $record = $this->visibleLead($request, $lead);
        $milestones = DB::table('lead_milestones')->where('lead_id', $lead)->where('is_active', true)->pluck('milestone')->all();
        $history = DB::table('lead_activity as a')->leftJoin('users as u', 'u.id', '=', 'a.actor_id')->where('a.lead_id', $lead)->orderByDesc('a.created_at')->limit(30)->get(['a.action', 'a.detail', 'a.created_at', 'u.name as actor_name']);
        $csAccounts = $request->user()->role === 'admin'
            ? DB::table('users as u')->join('branches as b', 'b.id', '=', 'u.branch_id')->where('u.role', 'cs')->where('u.active', true)->orderBy('b.name')->get(['u.id', 'u.name', 'b.name as branch_name'])
            : [];

        return Inertia::render('Leads/Show', ['lead' => $record, 'milestones' => $milestones, 'history' => $history, 'csAccounts' => $csAccounts]);
    }

    public function assign(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1', 'max:100'],
            'lead_ids.*' => ['integer', 'distinct', 'exists:leads,id'],
            'assigned_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);
        $owner = DB::table('users')->where('id', $data['assigned_user_id'])->where('role', 'cs')->where('active', true)->first();
        if (! $owner) {
            throw ValidationException::withMessages(['assigned_user_id' => 'CS aktif tidak valid.']);
        }

        $assigned = DB::transaction(function () use ($data, $request, $owner): int {
            $ids = DB::table('leads')->whereIn('id', $data['lead_ids'])->whereNull('archived_at')->pluck('id')->all();
            if ($ids === []) {
                return 0;
            }
            DB::table('leads')->whereIn('id', $ids)->update(['assigned_user_id' => $owner->id, 'updated_at' => now()]);
            DB::table('lead_activity')->insert(array_map(fn ($id) => [
                'lead_id' => $id,
                'actor_id' => $request->user()->id,
                'action' => 'Lead ditugaskan massal',
                'detail' => "Ditugaskan ke {$owner->name}.",
                'created_at' => now(),
            ], $ids));

            return count($ids);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$assigned} lead ditugaskan ke {$owner->name}."]);

        return to_route('leads.index');
    }

    public function update(UpdateLeadRequest $request, int $lead): RedirectResponse
    {
        $record = $this->visibleLead($request, $lead);
        abort_if($record->archived_at !== null, 404);
        $data = $request->validated();
        $selected = array_key_exists('milestones', $data)
            ? $data['milestones']
            : DB::table('lead_milestones')->where('lead_id', $lead)->where('is_active', true)->pluck('milestone')->all();
        $isAdmin = $request->user()->role === 'admin';

        if ($isAdmin && array_key_exists('assigned_user_id', $data) && $data['assigned_user_id']) {
            if (! DB::table('users')->where('id', $data['assigned_user_id'])->where('role', 'cs')->where('active', true)->exists()) {
                throw ValidationException::withMessages(['assigned_user_id' => 'CS tidak valid.']);
            }
        }

        DB::transaction(function () use ($request, $lead, $record, $data, $selected, $isAdmin): void {
            $changes = [];
            $updates = [];
            foreach (['intent_buying' => 'Intent buying', 'usage_date' => 'Waktu pemakaian', 'budget' => 'Budget', 'keterangan' => 'Keterangan', 'next_follow_up_at' => 'Tindak lanjut berikutnya'] as $field => $label) {
                if (($record->{$field} ?? null) !== ($data[$field] ?? null)) {
                    $updates[$field] = $data[$field] ?? null;
                    $changes[] = "{$label} diperbarui";
                }
            }
            if ($isAdmin && array_key_exists('assigned_user_id', $data) && (int) $record->assigned_user_id !== (int) ($data['assigned_user_id'] ?? 0)) {
                $updates['assigned_user_id'] = $data['assigned_user_id'] ?: null;
                $changes[] = $data['assigned_user_id'] ? 'CS penanggung jawab diperbarui' : 'Lead belum ditugaskan';
            }

            $active = DB::table('lead_milestones')->where('lead_id', $lead)->where('is_active', true)->pluck('milestone')->all();
            foreach (self::MILESTONES as $milestone => $label) {
                $enabled = in_array($milestone, $selected, true);
                if ($enabled === in_array($milestone, $active, true)) {
                    continue;
                }
                DB::table('lead_milestones')->updateOrInsert(
                    ['lead_id' => $lead, 'milestone' => $milestone],
                    ['is_active' => $enabled, 'marked_at' => now(), 'marked_by' => $request->user()->id],
                );
                $changes[] = "{$label} ".($enabled ? 'diaktifkan' : 'dihapus');
            }

            $currentStage = $this->currentStage($selected);
            if (($record->current_stage ?? null) !== $currentStage) {
                $updates['current_stage'] = $currentStage;
                $changes[] = 'Status kerja diperbarui';
            }

            if ($updates || $changes) {
                DB::table('leads')->where('id', $lead)->update($updates + ['updated_at' => now()]);
                DB::table('lead_activity')->insert(['lead_id' => $lead, 'actor_id' => $request->user()->id, 'action' => 'Lead diperbarui', 'detail' => implode('; ', $changes), 'created_at' => now()]);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead berhasil diperbarui.']);

        return to_route('leads.show', $lead);
    }

    public function archive(Request $request, int $lead): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $record = DB::table('leads')->where('id', $lead)->whereNull('archived_at')->first() ?? abort(404);
        DB::transaction(function () use ($record, $request): void {
            DB::table('leads')->where('id', $record->id)->update(['archived_at' => now(), 'updated_at' => now()]);
            DB::table('lead_activity')->insert(['lead_id' => $record->id, 'actor_id' => $request->user()->id, 'action' => 'Lead diarsipkan', 'detail' => 'Lead disembunyikan dari daftar kerja aktif.', 'created_at' => now()]);
        });
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead diarsipkan.']);

        return to_route('leads.index');
    }

    public function restore(Request $request, int $lead): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $record = DB::table('leads')->where('id', $lead)->whereNotNull('archived_at')->first() ?? abort(404);
        DB::transaction(function () use ($record, $request): void {
            DB::table('leads')->where('id', $record->id)->update(['archived_at' => null, 'updated_at' => now()]);
            DB::table('lead_activity')->insert(['lead_id' => $record->id, 'actor_id' => $request->user()->id, 'action' => 'Lead dipulihkan', 'detail' => 'Lead kembali ke daftar kerja aktif.', 'created_at' => now()]);
        });
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead dipulihkan.']);

        return to_route('leads.show', $lead);
    }

    /**
     * @return object{id: int, assigned_user_id: int|null, archived_at: string|null, current_stage: string|null, intent_buying: string|null, usage_date: string|null, budget: string|null, keterangan: string|null, next_follow_up_at: string|null}
     */
    private function visibleLead(Request $request, int $lead): object
    {
        $query = DB::table('leads')->where('id', $lead);
        if ($request->user()->role !== 'admin') {
            $query->where('assigned_user_id', $request->user()->id)->whereNull('archived_at');
        }

        $record = $query->first() ?? abort(404);

        /** @var object{id: int, assigned_user_id: int|null, archived_at: string|null, current_stage: string|null, intent_buying: string|null, usage_date: string|null, budget: string|null, keterangan: string|null, next_follow_up_at: string|null} $record */
        return $record;
    }

    private function contactKey(string $contact): string
    {
        $contact = trim($contact);
        $digits = preg_replace('/\D/', '', $contact) ?? '';

        return strlen($digits) >= 8 ? "phone:{$digits}" : 'contact:'.mb_strtolower($contact);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    private function initialMilestones(array $row): array
    {
        $text = mb_strtolower(trim(($row['imported_keterangan'] ?? '').' '.($row['imported_definition'] ?? '')));

        return match (true) {
            str_contains($text, 'repeat') => ['repeat_order'],
            str_contains($text, 'closing') && (str_contains($text, 'belum') || str_contains($text, 'transfer')) => ['closing_pending_transfer'],
            str_contains($text, 'closing') => ['closing'],
            str_contains($text, 'offline') || str_contains($text, 'datang') => ['offline'],
            str_contains($text, 'online') => ['online'],
            str_contains($text, 'pricelist') || str_contains($text, 'sampai pl') => ['price_list'],
            str_contains($text, 'tanya') || str_contains($text, 'warm') => ['asked'],
            str_contains($text, 'klik') || str_contains($text, 'cold') => ['clicked'],
            default => [],
        };
    }

    /**
     * @param  list<string>  $headers
     * @return array<string, string>
     */
    private function importHeaderMap(array $headers): array
    {
        $fields = [
            'no' => 'source_row_no',
            'tanggal' => 'source_date_raw',
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

        return collect($headers)->mapWithKeys(fn ($header) => [(string) $header => $fields[$this->normalizedHeader($header)] ?? null])->filter()->all();
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, string>  $headerMap
     * @return array<string, string|null>
     */
    private function normalizeImportRow(array $row, array $headerMap): array
    {
        $normalized = array_fill_keys([
            'source_row_no', 'source_date_raw', 'contact_raw', 'respond_time', 'imported_cs_name', 'imported_keterangan', 'raw_kebutuhan', 'greeting', 'chat_time', 'source_description', 'campaign_description', 'imported_definition',
        ], null);
        foreach ($headerMap as $header => $field) {
            $normalized[$field] = trim((string) ($row[$header] ?? '')) ?: null;
        }

        return $normalized;
    }

    private function normalizedHeader(string $header): string
    {
        return mb_strtolower(trim(str_replace('*', '', $header)));
    }

    /** @return array{0: list<string>, 1: list<array<int, string|null>>} */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw ValidationException::withMessages(['csv_file' => 'File CSV tidak dapat dibaca.']);
        }
        $headers = fgetcsv($handle) ?: [];
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($rows) >= self::IMPORT_ROW_LIMIT) {
                fclose($handle);
                throw ValidationException::withMessages(['csv_file' => 'Maksimal 5.000 baris lead per import. Pecah file lalu coba kembali.']);
            }
            $rows[] = $values;
        }
        fclose($handle);

        return [$this->cleanHeaders($headers), $rows];
    }

    /** @return array{0: list<string>, 1: list<array<int, string|null>>} */
    private function readXlsx(string $path): array
    {
        $reader = new XlsxReader;
        $reader->open($path);
        $headers = [];
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            $candidateHeaders = [];
            $candidateRows = [];
            foreach ($sheet->getRowIterator() as $row) {
                $values = array_map(
                    fn ($cell) => $this->spreadsheetValue($cell instanceof FormulaCell ? $cell->getComputedValue() : $cell->getValue()),
                    $row->cells,
                );
                if ($candidateHeaders === []) {
                    $candidateHeaders = $this->cleanHeaders($values);

                    continue;
                }
                if (count($candidateRows) >= self::IMPORT_ROW_LIMIT) {
                    $reader->close();
                    throw ValidationException::withMessages(['csv_file' => 'Maksimal 5.000 baris lead per import. Pecah file lalu coba kembali.']);
                }
                $candidateRows[] = $values;
            }
            if ($headers === [] || mb_strtolower($sheet->getName()) === 'all leads') {
                $headers = $candidateHeaders;
                $rows = $candidateRows;
            }
            if (mb_strtolower($sheet->getName()) === 'all leads') {
                break;
            }
        }
        $reader->close();

        return [$headers, $rows];
    }

    /**
     * @param  array<int, mixed>  $headers
     * @return list<string>
     */
    private function cleanHeaders(array $headers): array
    {
        return array_values(array_map(fn ($value) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $value)), $headers));
    }

    private function spreadsheetValue(mixed $value): string
    {
        return $value instanceof DateTimeInterface ? $value->format('Y-m-d H:i:s') : (string) $value;
    }

    private function csKey(string $name): string
    {
        $key = mb_strtolower(trim($name));

        return match ($key) {
            'bela' => 'bella',
            default => $key,
        };
    }

    /** @param array<string, mixed> $row */
    private function importClassification(array $row): ?string
    {
        $definition = mb_strtolower(trim((string) ($row['imported_definition'] ?? '')));
        if (in_array($definition, ['cold', 'warm', 'hot'], true)) {
            return ucfirst($definition);
        }

        return $this->classificationForMilestone($this->initialMilestones($row)[0] ?? null);
    }

    private function classificationForMilestone(?string $milestone): ?string
    {
        return match ($milestone) {
            'clicked' => 'Cold',
            'asked', 'price_list' => 'Warm',
            'offline', 'online', 'closing_pending_transfer', 'closing', 'repeat_order' => 'Hot',
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
}
