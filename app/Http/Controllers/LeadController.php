<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLeadRequest;
use App\Services\GoogleSheetSyncService;
use App\Services\LeadImportService;
use App\Support\LeadBudgetRanges;
use App\Support\LeadSourceDate;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        'asked' => 'Tanya-Tanya',
        'price_list' => 'Sampai Pricelist',
        'offline' => 'Datang Offline',
        'online' => 'Diskusi Online',
        'closing_repeat' => 'Closing / Repeat Order',
    ];

    public function __construct(private readonly LeadImportService $leadImporter) {}

    public function store(Request $request): RedirectResponse
    {
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
            'chat_time' => ['nullable', 'string', 'max:255'],
            'source_description' => ['nullable', 'string', 'max:160'],
            'campaign_description' => ['nullable', 'string', 'max:160'],
            'intent_buying' => ['nullable', 'in:Tunangan,Lamaran,Nikahan,Anniversary,Lainnya'],
            'usage_date' => ['nullable', 'date'],
            'budget' => ['nullable', Rule::in(LeadBudgetRanges::OPTIONS)],
            'keterangan' => ['nullable', 'string'],
            'next_follow_up_at' => ['nullable', 'date'],
            'milestone' => ['nullable', 'in:clicked,asked,price_list,offline,online,closing_repeat'],
        ]);
        $data['next_follow_up_at'] = $this->normalizedDateTime(
            isset($data['next_follow_up_at']) ? (string) $data['next_follow_up_at'] : null,
        );
        $isAdmin = $request->user()->role === 'admin';
        $assignedUserId = $isAdmin ? ($data['assigned_user_id'] ?? null) : $request->user()->id;
        $contact = ($data['phone'] ?? null) ?: $data['contact_raw'];
        $key = $this->contactKey($contact);
        if (DB::table('leads')->whereIn('contact_key', $this->contactKeys($contact))->exists()) {
            throw ValidationException::withMessages(['contact_raw' => 'Lead dengan kontak ini sudah ada.']);
        }
        if ($assignedUserId && ! DB::table('users')->where('id', $assignedUserId)->where('role', 'cs')->where('active', true)->exists()) {
            throw ValidationException::withMessages(['assigned_user_id' => 'CS aktif tidak valid.']);
        }

        $id = DB::transaction(function () use ($data, $key, $request, $assignedUserId, $isAdmin): int {
            $milestone = $data['milestone'] ?? null;
            $id = DB::table('leads')->insertGetId([
                'contact_key' => $key,
                'contact_raw' => $data['contact_raw'],
                'name' => $data['name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'assigned_user_id' => $assignedUserId,
                'source_date_raw' => $data['source_date_raw'] ?? null,
                'lead_created_on' => LeadSourceDate::parse($data['source_date_raw'] ?? null) ?? now()->toDateString(),
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
            DB::table('lead_activity')->insert(['lead_id' => $id, 'actor_id' => $request->user()->id, 'action' => 'Lead dibuat manual', 'detail' => $isAdmin ? ($assignedUserId ? 'Lead langsung ditugaskan ke CS.' : 'Lead belum ditugaskan ke CS.') : 'Lead dibuat oleh CS dan ditugaskan ke diri sendiri.', 'created_at' => now()]);

            return $id;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead ditambahkan.']);

        return $request->boolean('inline') ? back() : to_route('leads.show', $id);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $data = $request->validate(
            [
                'csv_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
                'import_mode' => ['nullable', 'in:all_leads,daily_tabs'],
                'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            ],
            ['csv_file.uploaded' => 'File gagal diunggah. Gunakan CSV/XLSX maksimal 10 MB.'],
        );

        $extension = strtolower($request->file('csv_file')->getClientOriginalExtension());
        $importMode = $data['import_mode'] ?? 'all_leads';
        if ($importMode === 'daily_tabs' && $extension !== 'xlsx') {
            throw ValidationException::withMessages(['csv_file' => 'Tab harian hanya dapat diimpor dari file XLSX.']);
        }
        if ($importMode === 'daily_tabs' && empty($data['branch_id'])) {
            throw ValidationException::withMessages(['branch_id' => 'Pilih cabang pemilik data harian.']);
        }
        if ($importMode === 'daily_tabs' && ! DB::table('users')->where('branch_id', $data['branch_id'])->where('role', 'cs')->where('active', true)->exists()) {
            throw ValidationException::withMessages(['branch_id' => 'Cabang sumber harus memiliki satu akun CS aktif sebelum data harian dapat diimpor.']);
        }
        [$headers, $valueRows] = match (true) {
            $importMode === 'daily_tabs' => $this->readDailyTabsXlsx($request->file('csv_file')->getRealPath()),
            $extension === 'xlsx' => $this->readXlsx($request->file('csv_file')->getRealPath()),
            default => $this->readCsv($request->file('csv_file')->getRealPath()),
        };
        $headerMap = $this->importHeaderMap($headers);
        if (! $this->hasRequiredImportHeaders($headerMap)) {
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
        $sourceBranchId = $importMode === 'daily_tabs' ? (int) $data['branch_id'] : null;
        $summary = $this->leadImporter->import($rows, $request->user()->id, null, $sourceBranchId);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Import selesai: {$summary['inserted']} baru, {$summary['skipped']} duplikat dilewati, {$summary['unassigned']} perlu ditugaskan, {$summary['needs_review']} perlu review, {$summary['missing_dates']} tanpa tanggal lead.",
        ]);

        return to_route('leads.index');
    }

    public function syncGoogleSheet(Request $request, GoogleSheetSyncService $sync): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);

        try {
            $summary = $sync->sync($request->user()->id);
        } catch (\RuntimeException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return to_route('leads.index');
        }

        $message = "Sinkronisasi selesai: {$summary['inserted']} lead baru, {$summary['resurfaced']} muncul kembali.";
        if ($summary['missing_dates']) {
            $message .= " {$summary['missing_dates']} lead baru tanpa tanggal lead; periksa format tanggal sumber.";
        }
        if ($summary['failed']) {
            $message .= " {$summary['failed']} cabang gagal disinkronkan; periksa statusnya di menu Cabang.";
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $message,
        ]);

        return to_route('leads.index');
    }

    public function show(Request $request, int $lead): Response
    {
        $record = $this->visibleLead($request, $lead, null);
        $milestones = DB::table('lead_milestones')->where('lead_id', $lead)->where('is_active', true)->pluck('milestone')->all();
        $history = DB::table('lead_activity as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.actor_id')
            ->where('a.lead_id', $lead)
            ->orderByDesc('a.created_at')
            ->orderByDesc('a.id')
            ->paginate(30, ['a.action', 'a.detail', 'a.created_at', 'u.name as actor_name'], 'history_page')
            ->withQueryString();
        $csAccounts = $request->user()->role === 'admin'
            ? DB::table('users as u')->join('branches as b', 'b.id', '=', 'u.branch_id')->where('u.role', 'cs')->where('u.active', true)->orderBy('b.name')->get(['u.id', 'u.name', 'b.name as branch_name'])
            : [];
        $collaborators = DB::table('lead_collaborators as c')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->join('branches as b', 'b.id', '=', 'u.branch_id')
            ->where('c.lead_id', $lead)
            ->whereNull('c.removed_at')
            ->orderBy('b.name')
            ->get(['u.id', 'u.name', 'b.name as branch_name']);

        return Inertia::render('Leads/Show', ['lead' => $record, 'milestones' => $milestones, 'history' => $history, 'csAccounts' => $csAccounts, 'collaborators' => $collaborators]);
    }

    public function historyPreview(Request $request, int $lead): JsonResponse
    {
        $this->visibleLead($request, $lead, null);

        $history = DB::table('lead_activity as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.actor_id')
            ->where('a.lead_id', $lead)
            ->orderByDesc('a.created_at')
            ->orderByDesc('a.id')
            ->limit(5)
            ->get(['a.action', 'a.detail', 'a.created_at', 'u.name as actor_name'])
            ->map(static fn (object $activity): array => [
                'action' => (string) $activity->action,
                'detail' => $activity->detail === null ? null : (string) $activity->detail,
                'created_at' => $activity->created_at === null ? null : (string) $activity->created_at,
                'actor_name' => $activity->actor_name === null ? null : (string) $activity->actor_name,
            ])
            ->values()
            ->all();

        return response()->json(['data' => $history]);
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
            DB::table('leads')->whereIn('id', $ids)->update(['assigned_user_id' => $owner->id, 'updated_at' => now(), 'revision' => DB::raw('revision + 1')]);
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

    public function addCollaboratorSelected(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1', 'max:100'],
            'lead_ids.*' => ['integer', 'distinct', 'exists:leads,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);
        $user = DB::table('users')->where('id', $data['user_id'])->where('role', 'cs')->where('active', true)->first(['id', 'name']);
        if (! $user) {
            throw ValidationException::withMessages(['user_id' => 'Pilih akun CS aktif.']);
        }

        $summary = DB::transaction(function () use ($data, $request, $user): array {
            $leads = DB::table('leads')->whereIn('id', $data['lead_ids'])->whereNull('archived_at')->get(['id', 'assigned_user_id']);
            abort_unless($leads->count() === count($data['lead_ids']), 404);
            $leadIds = $leads->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();
            $existing = DB::table('lead_collaborators')->whereIn('lead_id', $leadIds)->where('user_id', $user->id)->whereNull('removed_at')->pluck('lead_id')->map(static fn (mixed $id): int => (int) $id)->all();
            $eligible = $leads->filter(static fn (object $lead): bool => (int) $lead->assigned_user_id !== (int) $user->id && ! in_array((int) $lead->id, $existing, true))->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();
            $now = now();

            foreach ($eligible as $leadId) {
                DB::table('lead_collaborators')->updateOrInsert(
                    ['lead_id' => $leadId, 'user_id' => $user->id],
                    ['added_by' => $request->user()->id, 'added_at' => $now, 'removed_at' => null],
                );
            }
            if ($eligible !== []) {
                DB::table('leads')->whereIn('id', $eligible)->update(['updated_at' => $now, 'revision' => DB::raw('revision + 1')]);
                DB::table('lead_activity')->insert(array_map(fn (int $leadId): array => [
                    'lead_id' => $leadId,
                    'actor_id' => $request->user()->id,
                    'action' => 'CS pendamping ditambahkan',
                    'detail' => "{$user->name} ditambahkan sebagai CS pendamping.",
                    'created_at' => $now,
                ], $eligible));
            }

            return ['added' => count($eligible), 'skipped' => count($leadIds) - count($eligible)];
        });

        $message = "{$summary['added']} lead ditambahkan ke penanganan {$user->name}.";
        if ($summary['skipped'] > 0) {
            $message .= " {$summary['skipped']} lead sudah ditangani CS tersebut atau menjadi CS utama.";
        }
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('leads.index');
    }

    public function updateCollaborators(Request $request, int $lead): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $record = $this->visibleLead($request, $lead, false);
        $data = $request->validate([
            'user_ids' => ['present', 'array', 'max:10'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);
        $userIds = array_values(array_map('intval', $data['user_ids']));
        if ($record->assigned_user_id !== null && in_array((int) $record->assigned_user_id, $userIds, true)) {
            throw ValidationException::withMessages(['user_ids' => 'CS utama tidak perlu ditambahkan sebagai CS pendamping.']);
        }
        $users = DB::table('users')->whereIn('id', $userIds)->where('role', 'cs')->where('active', true)->get(['id', 'name']);
        if ($users->count() !== count($userIds)) {
            throw ValidationException::withMessages(['user_ids' => 'Pilih hanya akun CS aktif.']);
        }

        DB::transaction(function () use ($lead, $request, $userIds): void {
            $currentIds = DB::table('lead_collaborators')->where('lead_id', $lead)->whereNull('removed_at')->pluck('user_id')->map(static fn (mixed $id): int => (int) $id)->all();
            $addedIds = array_values(array_diff($userIds, $currentIds));
            $removedIds = array_values(array_diff($currentIds, $userIds));
            $now = now();

            if ($removedIds !== []) {
                DB::table('lead_collaborators')->where('lead_id', $lead)->whereIn('user_id', $removedIds)->whereNull('removed_at')->update(['removed_at' => $now]);
            }
            foreach ($addedIds as $userId) {
                DB::table('lead_collaborators')->updateOrInsert(
                    ['lead_id' => $lead, 'user_id' => $userId],
                    ['added_by' => $request->user()->id, 'added_at' => $now, 'removed_at' => null],
                );
            }
            if ($addedIds === [] && $removedIds === []) {
                return;
            }

            $names = DB::table('users')->whereIn('id', array_values(array_unique([...$addedIds, ...$removedIds])))->pluck('name', 'id');
            $details = [];
            if ($addedIds !== []) {
                $details[] = 'Ditambahkan: '.implode(', ', array_map(static fn (int $id): string => (string) ($names[$id] ?? $id), $addedIds));
            }
            if ($removedIds !== []) {
                $details[] = 'Dilepas: '.implode(', ', array_map(static fn (int $id): string => (string) ($names[$id] ?? $id), $removedIds));
            }
            DB::table('lead_activity')->insert(['lead_id' => $lead, 'actor_id' => $request->user()->id, 'action' => 'CS pendamping diperbarui', 'detail' => implode('. ', $details), 'created_at' => $now]);
            DB::table('leads')->where('id', $lead)->update(['updated_at' => $now, 'revision' => DB::raw('revision + 1')]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tim penanganan lead diperbarui.']);

        return back();
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
        if (! $isAdmin && array_key_exists('crm_taken_over', $data)) {
            abort(403);
        }

        DB::transaction(function () use ($request, $lead, $record, $data, $selected, $isAdmin): void {
            if (array_key_exists('revision', $data)) {
                $revision = (int) DB::table('leads')->where('id', $lead)->lockForUpdate()->value('revision');
                if ($revision !== (int) $data['revision']) {
                    throw ValidationException::withMessages(['revision' => 'Lead sudah diperbarui CS lain. Buka detail lead untuk meninjau perubahan terbaru.']);
                }
            }
            $changes = [];
            $updates = [];
            foreach (['intent_buying' => 'Intent buying', 'usage_date' => 'Waktu pemakaian', 'budget' => 'Budget', 'keterangan' => 'Keterangan', 'next_follow_up_at' => 'Tindak lanjut berikutnya'] as $field => $label) {
                if (! array_key_exists($field, $data)) {
                    continue;
                }
                $value = $field === 'next_follow_up_at'
                    ? $this->normalizedDateTime(isset($data[$field]) ? (string) $data[$field] : null)
                    : ($data[$field] ?? null);
                if (($record->{$field} ?? null) !== $value) {
                    $updates[$field] = $value;
                    $changes[] = "{$label} diperbarui";
                }
            }
            if ($isAdmin && array_key_exists('assigned_user_id', $data) && (int) $record->assigned_user_id !== (int) ($data['assigned_user_id'] ?? 0)) {
                $updates['assigned_user_id'] = $data['assigned_user_id'] ?: null;
                if ($data['assigned_user_id']) {
                    DB::table('lead_collaborators')->where('lead_id', $lead)->where('user_id', $data['assigned_user_id'])->whereNull('removed_at')->update(['removed_at' => now()]);
                }
                $changes[] = $data['assigned_user_id'] ? 'CS penanggung jawab diperbarui' : 'Lead belum ditugaskan';
            }
            if ($isAdmin && array_key_exists('crm_taken_over', $data)) {
                $takenOver = (bool) $data['crm_taken_over'];
                if (($record->crm_taken_over_at !== null) !== $takenOver) {
                    $updates['crm_taken_over_at'] = $takenOver ? now() : null;
                    $changes[] = $takenOver ? 'Lead ditandai diambil alih CRM' : 'Penanda pengambilalihan CRM dibatalkan';
                }
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
                DB::table('leads')->where('id', $lead)->update($updates + ['updated_at' => now(), 'revision' => DB::raw('revision + 1')]);
                DB::table('lead_activity')->insert(['lead_id' => $lead, 'actor_id' => $request->user()->id, 'action' => 'Lead diperbarui', 'detail' => implode('; ', $changes), 'created_at' => now()]);
            }
        });

        if (! $request->boolean('inline')) {
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead berhasil diperbarui.']);
        }

        return $request->boolean('inline') ? back() : to_route('leads.show', $lead);
    }

    public function archive(Request $request, int $lead): RedirectResponse
    {
        $record = $this->visibleLead($request, $lead, false);
        DB::transaction(function () use ($record, $request): void {
            DB::table('leads')->where('id', $record->id)->update(['archived_at' => now(), 'updated_at' => now(), 'revision' => DB::raw('revision + 1')]);
            DB::table('lead_activity')->insert(['lead_id' => $record->id, 'actor_id' => $request->user()->id, 'action' => 'Lead diarsipkan', 'detail' => 'Lead disembunyikan dari daftar kerja aktif.', 'created_at' => now()]);
        });
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead diarsipkan.']);

        return to_route('leads.index');
    }

    public function restore(Request $request, int $lead): RedirectResponse
    {
        $record = $this->visibleLead($request, $lead, true);
        DB::transaction(function () use ($record, $request): void {
            DB::table('leads')->where('id', $record->id)->update(['archived_at' => null, 'updated_at' => now(), 'revision' => DB::raw('revision + 1')]);
            DB::table('lead_activity')->insert(['lead_id' => $record->id, 'actor_id' => $request->user()->id, 'action' => 'Lead dipulihkan', 'detail' => 'Lead kembali ke daftar kerja aktif.', 'created_at' => now()]);
        });
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead dipulihkan.']);

        return to_route('leads.show', $lead);
    }

    public function archiveSelected(Request $request): RedirectResponse
    {
        $ids = $this->selectedLeadIds($request, false);
        DB::transaction(function () use ($ids, $request): void {
            DB::table('leads')->whereIn('id', $ids)->update(['archived_at' => now(), 'updated_at' => now(), 'revision' => DB::raw('revision + 1')]);
            DB::table('lead_activity')->insert(array_map(fn ($id) => [
                'lead_id' => $id,
                'actor_id' => $request->user()->id,
                'action' => 'Lead diarsipkan massal',
                'detail' => 'Lead dipindahkan dari daftar kerja aktif.',
                'created_at' => now(),
            ], $ids));
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => count($ids).' lead diarsipkan.']);

        return to_route('leads.index');
    }

    public function restoreSelected(Request $request): RedirectResponse
    {
        $ids = $this->selectedLeadIds($request, true);
        DB::transaction(function () use ($ids, $request): void {
            DB::table('leads')->whereIn('id', $ids)->update(['archived_at' => null, 'updated_at' => now(), 'revision' => DB::raw('revision + 1')]);
            DB::table('lead_activity')->insert(array_map(fn ($id) => [
                'lead_id' => $id,
                'actor_id' => $request->user()->id,
                'action' => 'Lead dipulihkan massal',
                'detail' => 'Lead kembali ke daftar kerja aktif.',
                'created_at' => now(),
            ], $ids));
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => count($ids).' lead dipulihkan.']);

        return to_route('leads.index', ['archived' => 1]);
    }

    public function destroy(Request $request, int $lead): RedirectResponse
    {
        $record = $this->visibleLead($request, $lead, true);
        $this->deleteLeadRecords([$record->id]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead dihapus permanen.']);

        return to_route('leads.index', ['archived' => 1]);
    }

    public function destroySelected(Request $request): RedirectResponse
    {
        $ids = $this->selectedLeadIds($request, true);
        $this->deleteLeadRecords($ids);

        Inertia::flash('toast', ['type' => 'success', 'message' => count($ids).' lead dihapus permanen.']);

        return to_route('leads.index', ['archived' => 1]);
    }

    /**
     * @return object{id: int, assigned_user_id: int|null, archived_at: string|null, crm_taken_over_at: string|null, revision: int, current_stage: string|null, intent_buying: string|null, usage_date: string|null, budget: string|null, keterangan: string|null, next_follow_up_at: string|null}
     */
    private function visibleLead(Request $request, int $lead, ?bool $archived = false): object
    {
        $query = DB::table('leads')->where('id', $lead);
        $this->restrictToVisibleLeads($query, $request);
        if ($archived !== null) {
            $query->{$archived ? 'whereNotNull' : 'whereNull'}('archived_at');
        }

        $record = $query->first() ?? abort(404);

        /** @var object{id: int, assigned_user_id: int|null, archived_at: string|null, crm_taken_over_at: string|null, revision: int, current_stage: string|null, intent_buying: string|null, usage_date: string|null, budget: string|null, keterangan: string|null, next_follow_up_at: string|null} $record */
        return $record;
    }

    /** @return list<int> */
    private function selectedLeadIds(Request $request, bool $archived): array
    {
        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1', 'max:100'],
            'lead_ids.*' => ['integer', 'distinct', 'exists:leads,id'],
        ]);
        $query = DB::table('leads')->whereIn('id', $data['lead_ids']);
        $query->{$archived ? 'whereNotNull' : 'whereNull'}('archived_at');
        $this->restrictToVisibleLeads($query, $request);
        $ids = array_values($query->pluck('id')->map(fn ($id) => (int) $id)->all());
        abort_unless(count($ids) === count($data['lead_ids']), 404);

        return $ids;
    }

    /** @param list<int> $ids */
    private function deleteLeadRecords(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            DB::table('lead_collaborators')->whereIn('lead_id', $ids)->delete();
            DB::table('lead_activity')->whereIn('lead_id', $ids)->delete();
            DB::table('lead_milestones')->whereIn('lead_id', $ids)->delete();
            DB::table('leads')->whereIn('id', $ids)->delete();
        });
    }

    private function restrictToVisibleLeads(Builder $query, Request $request): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        $userId = $request->user()->id;
        $query->where(function (Builder $visible) use ($userId): void {
            $visible->where('assigned_user_id', $userId)->orWhereExists(function (Builder $collaborator) use ($userId): void {
                $collaborator->selectRaw('1')
                    ->from('lead_collaborators')
                    ->whereColumn('lead_collaborators.lead_id', 'leads.id')
                    ->where('lead_collaborators.user_id', $userId)
                    ->whereNull('lead_collaborators.removed_at');
            });
        });
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
            'tanggal sumber' => 'source_date_raw',
            'tab sumber' => 'source_tab',
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
            'source_row_no', 'source_date_raw', 'source_tab', 'contact_raw', 'respond_time', 'imported_cs_name', 'imported_keterangan', 'raw_kebutuhan', 'greeting', 'chat_time', 'source_description', 'campaign_description', 'imported_definition',
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

    /** @return array{0: list<string>, 1: list<array<int, string|null>>} */
    private function readDailyTabsXlsx(string $path): array
    {
        $reader = new XlsxReader;
        $reader->open($path);
        $headers = [];
        $rows = [];
        $foundDailyTab = false;

        foreach ($reader->getSheetIterator() as $sheet) {
            if (! preg_match('/^\d{1,2}$/', $sheet->getName())) {
                continue;
            }
            $foundDailyTab = true;
            $sourceDate = '';
            $dailyHeaders = [];
            $rowNumber = 0;

            foreach ($sheet->getRowIterator() as $row) {
                $rowNumber++;
                $values = array_map(
                    fn ($cell) => $this->spreadsheetValue($cell instanceof FormulaCell ? $cell->getComputedValue() : $cell->getValue()),
                    $row->cells,
                );
                if ($rowNumber === 2) {
                    $sourceDate = $values[1] ?? '';
                }
                if ($rowNumber === 4) {
                    $dailyHeaders = $this->cleanHeaders(array_slice($values, 15, 10));

                    continue;
                }
                if ($rowNumber <= 4) {
                    continue;
                }

                $dailyValues = array_slice($values, 15, 10);
                if (trim((string) ($dailyValues[1] ?? '')) === '') {
                    continue;
                }
                if (count($rows) >= self::IMPORT_ROW_LIMIT) {
                    $reader->close();
                    throw ValidationException::withMessages(['csv_file' => 'Maksimal 5.000 lead total dari seluruh tab harian. Pecah file lalu coba kembali.']);
                }
                $rows[] = [...$dailyValues, $sourceDate, $sheet->getName()];
            }

            if (! $this->hasRequiredImportHeaders($this->importHeaderMap($dailyHeaders))) {
                $reader->close();
                throw ValidationException::withMessages(['csv_file' => "Struktur lead utama pada tab {$sheet->getName()} belum sesuai."]);
            }

            $dailyHeaders = [...$dailyHeaders, 'Tanggal sumber', 'Tab sumber'];
            if ($headers === []) {
                $headers = $dailyHeaders;
            } elseif ($headers !== $dailyHeaders) {
                $reader->close();
                throw ValidationException::withMessages(['csv_file' => "Header tab {$sheet->getName()} berbeda dari tab harian lainnya."]);
            }
        }
        $reader->close();

        if (! $foundDailyTab) {
            throw ValidationException::withMessages(['csv_file' => 'Tidak menemukan tab harian bernama angka, misalnya 1 sampai 31.']);
        }

        return [$headers, $rows];
    }

    /** @param array<string, string> $headerMap */
    private function hasRequiredImportHeaders(array $headerMap): bool
    {
        $required = ['contact_raw', 'respond_time', 'imported_cs_name', 'imported_keterangan', 'raw_kebutuhan', 'greeting', 'chat_time', 'source_description', 'campaign_description'];

        return array_diff($required, array_values($headerMap)) === []
            && (in_array('source_date_raw', $headerMap, true) || in_array('source_row_no', $headerMap, true));
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

    private function normalizedDateTime(?string $value): ?string
    {
        return $value ? (new DateTimeImmutable($value))->format('Y-m-d H:i:s') : null;
    }

    private function classificationForMilestone(?string $milestone): ?string
    {
        return match ($milestone) {
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
}
