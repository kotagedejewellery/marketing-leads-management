<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetSyncService;
use App\Support\LeadBudgetRanges;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function dashboard(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'priorities' => $this->priorities($request),
            'recent_leads' => $this->recentLeads($request),
        ]);
    }

    public function leads(Request $request): Response
    {
        $this->validateLeadFilters($request);
        $query = DB::table('leads as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.assigned_user_id')
            ->leftJoin('branches as b', 'b.id', '=', 'u.branch_id')
            ->leftJoin('branches as source_b', 'source_b.id', '=', 'l.source_branch_id')
            ->leftJoin('branches as conflict_b', 'conflict_b.id', '=', 'l.source_conflict_branch_id')
            ->select('l.id', 'l.name', 'l.contact_raw', 'l.phone', 'l.intent_buying', 'l.usage_date', 'l.budget', 'l.keterangan', 'l.imported_definition', 'l.current_stage', 'l.next_follow_up_at', 'l.crm_taken_over_at', 'l.revision', 'l.source_conflict_at', 'l.lead_created_on', 'l.created_at', 'l.updated_at', 'u.name as owner_name', DB::raw('COALESCE(b.name, source_b.name) as branch_name'), 'conflict_b.name as source_conflict_branch_name')
            ->orderByDesc('l.updated_at');

        $showArchived = $request->boolean('archived');
        $query->{$showArchived ? 'whereNotNull' : 'whereNull'}('l.archived_at');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(fn ($builder) => $builder->whereRaw('l.name ILIKE ?', [$term])->orWhereRaw('l.phone ILIKE ?', [$term])->orWhereRaw('l.contact_raw ILIKE ?', [$term]));
        }
        if ($request->user()->role === 'admin' && $request->filled('branch')) {
            $query->whereRaw('COALESCE(u.branch_id, l.source_branch_id) = ?', [$request->integer('branch')]);
        }
        if ($request->filled('stage')) {
            $query->where('l.current_stage', $request->string('stage')->toString());
        }
        if ($request->filled('intent')) {
            $query->where('l.intent_buying', $request->string('intent')->toString());
        }
        if ($request->filled('budget')) {
            $query->where('l.budget', $request->string('budget')->toString());
        }
        if ($request->filled('lead_date_from')) {
            $query->whereDate('l.lead_created_on', '>=', $request->string('lead_date_from')->toString());
        }
        if ($request->filled('lead_date_to')) {
            $query->whereDate('l.lead_created_on', '<=', $request->string('lead_date_to')->toString());
        }
        if ($request->filled('lead_date_status')) {
            match ($request->string('lead_date_status')->toString()) {
                'known' => $query->whereNotNull('l.lead_created_on'),
                'missing' => $query->whereNull('l.lead_created_on'),
                default => null,
            };
        }
        if ($request->filled('follow_up')) {
            match ($request->string('follow_up')->toString()) {
                'scheduled' => $query->whereNotNull('l.next_follow_up_at'),
                'none' => $query->whereNull('l.next_follow_up_at'),
                'today' => $query->whereBetween('l.next_follow_up_at', [now()->startOfDay(), now()->endOfDay()]),
                'tomorrow' => $query->whereBetween('l.next_follow_up_at', [now()->addDay()->startOfDay(), now()->addDay()->endOfDay()]),
                'overdue' => $query->whereNotNull('l.next_follow_up_at')->where('l.next_follow_up_at', '<=', now()),
                default => null,
            };
        }
        if ($request->user()->role === 'cs' && $request->filled('handling')) {
            $userId = $request->user()->id;
            match ($request->string('handling')->toString()) {
                'primary' => $query->where('l.assigned_user_id', $userId),
                'collaborator' => $query->whereExists(function (Builder $collaborator) use ($userId): void {
                    $collaborator->selectRaw('1')
                        ->from('lead_collaborators as c')
                        ->whereColumn('c.lead_id', 'l.id')
                        ->where('c.user_id', $userId)
                        ->whereNull('c.removed_at');
                }),
                default => null,
            };
        }
        if ($request->filled('crm')) {
            match ($request->string('crm')->toString()) {
                'taken_over' => $query->whereNotNull('l.crm_taken_over_at'),
                'not_taken_over' => $query->whereNull('l.crm_taken_over_at'),
                default => null,
            };
        }
        if ($request->filled('queue')) {
            match ($request->string('queue')->toString()) {
                'unassigned' => $request->user()->role === 'admin' ? $query->whereNull('l.assigned_user_id') : null,
                'needs_qualification' => $query->where(fn (Builder $builder) => $builder->whereNull('l.intent_buying')->orWhereNull('l.usage_date')->orWhereNull('l.budget')),
                'follow_up_due' => $query->whereNotNull('l.next_follow_up_at')->where('l.next_follow_up_at', '<=', now()),
                'crm_taken_over' => $query->whereNotNull('l.crm_taken_over_at'),
                'source_conflict' => $request->user()->role === 'admin' ? $query->whereNotNull('l.source_conflict_at') : null,
                default => null,
            };
        }
        $this->restrictToVisibleLeads($query, $request, 'l');

        $leads = $query->paginate(20)->withQueryString();
        $leadIds = $leads->pluck('id')->all();
        $milestonesByLead = [];
        foreach (DB::table('lead_milestones')->whereIn('lead_id', $leadIds)->where('is_active', true)->orderBy('milestone')->get(['lead_id', 'milestone']) as $milestone) {
            $milestonesByLead[$milestone->lead_id][] = $milestone->milestone;
        }
        $latestActivityByLead = [];
        $rankedActivities = DB::table('lead_activity')
            ->select('lead_id', 'action', 'detail', 'created_at')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY lead_id ORDER BY created_at DESC, id DESC) as activity_rank')
            ->whereIn('lead_id', $leadIds);
        foreach (DB::query()->fromSub($rankedActivities, 'ranked_activities')->where('activity_rank', 1)->get() as $activity) {
            $latestActivityByLead[$activity->lead_id] = $activity;
        }
        $collaboratorsByLead = [];
        foreach (DB::table('lead_collaborators as c')->join('users as u', 'u.id', '=', 'c.user_id')->whereIn('c.lead_id', $leadIds)->whereNull('c.removed_at')->orderBy('u.name')->get(['c.lead_id', 'u.id', 'u.name']) as $collaborator) {
            $collaboratorsByLead[$collaborator->lead_id][] = ['id' => (int) $collaborator->id, 'name' => (string) $collaborator->name];
        }
        foreach ($leads->items() as $lead) {
            $lead->milestones = $milestonesByLead[$lead->id] ?? [];
            $lead->collaborators = $collaboratorsByLead[$lead->id] ?? [];
            $activity = $latestActivityByLead[$lead->id] ?? null;
            $lead->latest_activity = $activity->action ?? null;
            $lead->latest_activity_detail = $activity->detail ?? null;
            $lead->latest_activity_at = $activity->created_at ?? null;
        }

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'filters' => $request->only('q', 'branch', 'stage', 'queue', 'intent', 'budget', 'lead_date_from', 'lead_date_to', 'lead_date_status', 'follow_up', 'handling', 'crm', 'archived'),
            'archiveMode' => $showArchived,
            'branches' => $request->user()->role === 'admin' ? DB::table('branches')->orderBy('name')->get(['id', 'name']) : [],
            'csAccounts' => $request->user()->role === 'admin'
                ? DB::table('users as u')->join('branches as b', 'b.id', '=', 'u.branch_id')->where('u.role', 'cs')->where('u.active', true)->orderBy('b.name')->get(['u.id', 'u.name', 'b.name as branch_name'])
                : [],
            'sheetSync' => $request->user()->role === 'admin'
                ? DB::table('lead_sync_runs')->where('source', 'google_sheets')->orderByDesc('completed_at')->first(['status', 'completed_at'])
                : null,
        ]);
    }

    public function statistics(Request $request): Response
    {
        return Inertia::render('Statistics', [
            'stats' => $this->stats($request),
            'quality' => $this->quality($request),
            'workload' => $request->user()->role === 'admin' ? $this->workload() : [],
        ]);
    }

    public function branches(): Response
    {
        return Inertia::render('Branches/Index', ['branches' => DB::table('branches as b')->leftJoin('users as u', fn ($join) => $join->on('u.branch_id', '=', 'b.id')->where('u.role', 'cs')->where('u.active', true))->orderBy('b.name')->get(['b.id', 'b.name', 'b.google_spreadsheet_id', 'b.google_sheets_synced_at', 'b.google_sheets_sync_error', 'u.name as cs_name', 'u.email as cs_email'])]);
    }

    public function accounts(): Response
    {
        return Inertia::render('Accounts/Index', ['accounts' => DB::table('users as u')->leftJoin('branches as b', 'b.id', '=', 'u.branch_id')->orderByDesc('u.active')->orderBy('u.name')->get(['u.id', 'u.name', 'u.email', 'u.role', 'u.branch_id', 'u.active', 'b.name as branch_name']), 'branches' => DB::table('branches')->orderBy('name')->get(['id', 'name'])]);
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $data = $this->branchData($request);
        DB::table('branches')->insert(['name' => $data['name'], 'google_spreadsheet_id' => $data['google_spreadsheet_id'], 'created_at' => now()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cabang ditambahkan.']);

        return back();
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $data = $this->accountData($request, null, true);
        if ($data['password'] === null) {
            throw ValidationException::withMessages(['password' => 'Kata sandi wajib diisi.']);
        }
        DB::table('users')->insert(['name' => $data['name'], 'email' => $data['email'], 'password_hash' => bcrypt($data['password']), 'role' => $data['role'], 'branch_id' => $data['role'] === 'cs' ? $data['branch_id'] : null, 'created_at' => now()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Akun ditambahkan.']);

        return back();
    }

    public function updateBranch(Request $request, int $branch): RedirectResponse
    {
        $record = DB::table('branches')->where('id', $branch)->first() ?? abort(404);
        $data = $this->branchData($request, $record->id);
        DB::table('branches')->where('id', $branch)->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cabang diperbarui.']);

        return back();
    }

    /** @return array{name: string, google_spreadsheet_id: string|null} */
    private function branchData(Request $request, ?int $branchId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('branches', 'name')->ignore($branchId)],
            'google_spreadsheet_id' => ['nullable', 'string', 'max:128', Rule::unique('branches', 'google_spreadsheet_id')->ignore($branchId)],
        ]);

        return ['name' => (string) $data['name'], 'google_spreadsheet_id' => trim((string) ($data['google_spreadsheet_id'] ?? '')) ?: null];
    }

    public function destroyBranch(int $branch): RedirectResponse
    {
        $record = DB::table('branches')->where('id', $branch)->first() ?? abort(404);
        if (DB::table('users')->where('branch_id', $record->id)->exists()) {
            throw ValidationException::withMessages(['branch' => 'Cabang masih memiliki akun. Pindahkan akun terlebih dahulu.']);
        }
        if (DB::table('leads')->where('source_branch_id', $record->id)->exists()) {
            throw ValidationException::withMessages(['branch' => 'Cabang masih menjadi sumber lead. Pertahankan jejak asal data tersebut.']);
        }
        DB::table('branches')->where('id', $record->id)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cabang dihapus.']);

        return back();
    }

    public function syncBranchGoogleSheet(Request $request, GoogleSheetSyncService $sync, int $branch): RedirectResponse
    {
        abort_unless(DB::table('branches')->where('id', $branch)->exists(), 404);

        try {
            $summary = $sync->sync($request->user()->id, $branch);
        } catch (\RuntimeException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => $summary['failed'] ? 'error' : 'success', 'message' => $summary['failed'] ? 'Sinkronisasi cabang gagal. Periksa konfigurasi atau akun CS.' : "Sinkronisasi cabang selesai: {$summary['inserted']} lead baru."]);

        return back();
    }

    public function updateAccount(Request $request, int $account): RedirectResponse
    {
        $record = DB::table('users')->where('id', $account)->first() ?? abort(404);
        $active = $request->boolean('active', (bool) $record->active);
        $data = $this->accountData($request, $record->id, false, $active);
        if ($record->id === $request->user()->id && ! $active) {
            throw ValidationException::withMessages(['active' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }
        if ($record->role === 'admin' && $record->active && ($data['role'] !== 'admin' || ! $active) && DB::table('users')->where('role', 'admin')->where('active', true)->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'Minimal satu admin aktif harus tersedia.']);
        }

        DB::transaction(function () use ($record, $data, $active, $request): void {
            $updates = ['name' => $data['name'], 'email' => $data['email'], 'role' => $data['role'], 'branch_id' => $data['role'] === 'cs' ? $data['branch_id'] : null, 'active' => $active];
            if ($data['password']) {
                $updates['password_hash'] = bcrypt($data['password']);
            }
            DB::table('users')->where('id', $record->id)->update($updates);
            if ((! $active || $data['role'] !== 'cs') && $record->role === 'cs') {
                $now = now();
                $primaryLeadIds = DB::table('leads')->where('assigned_user_id', $record->id)->pluck('id')->all();
                $collaboratorLeadIds = DB::table('lead_collaborators')->where('user_id', $record->id)->whereNull('removed_at')->pluck('lead_id')->all();
                DB::table('leads')->whereIn('id', $primaryLeadIds)->update(['assigned_user_id' => null, 'updated_at' => $now, 'revision' => DB::raw('revision + 1')]);
                DB::table('lead_collaborators')->where('user_id', $record->id)->whereNull('removed_at')->update(['removed_at' => $now]);
                DB::table('leads')->whereIn('id', $collaboratorLeadIds)->update(['updated_at' => $now, 'revision' => DB::raw('revision + 1')]);
                $activity = [];
                foreach ($primaryLeadIds as $leadId) {
                    $activity[] = ['lead_id' => $leadId, 'actor_id' => $request->user()->id, 'action' => 'CS utama dilepas', 'detail' => "{$record->name} tidak lagi aktif sebagai CS.", 'created_at' => $now];
                }
                foreach ($collaboratorLeadIds as $leadId) {
                    $activity[] = ['lead_id' => $leadId, 'actor_id' => $request->user()->id, 'action' => 'CS pendamping dilepas', 'detail' => "{$record->name} tidak lagi aktif sebagai CS.", 'created_at' => $now];
                }
                if ($activity !== []) {
                    DB::table('lead_activity')->insert($activity);
                }
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => $active ? 'Akun diperbarui.' : 'Akun dinonaktifkan dan lead terkait dilepas.']);

        return back();
    }

    /**
     * @return array{name: string, email: string, password: string|null, role: 'admin'|'cs', branch_id: int|null}
     */
    private function accountData(Request $request, ?int $accountId = null, bool $passwordRequired = false, bool $willBeActive = true): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($accountId)],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'min:12'],
            'role' => ['required', 'in:admin,cs'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);
        if ($data['role'] === 'cs') {
            if (! ($data['branch_id'] ?? false)) {
                throw ValidationException::withMessages(['branch_id' => 'Cabang wajib dipilih untuk akun CS.']);
            }
            $activeCs = DB::table('users')->where('role', 'cs')->where('active', true)->where('branch_id', $data['branch_id']);
            if ($accountId) {
                $activeCs->where('id', '!=', $accountId);
            }
            if ($willBeActive && $activeCs->exists()) {
                throw ValidationException::withMessages(['branch_id' => 'Cabang sudah memiliki akun CS aktif.']);
            }
        }

        return [
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => isset($data['password']) ? (string) $data['password'] : null,
            'role' => $data['role'] === 'admin' ? 'admin' : 'cs',
            'branch_id' => isset($data['branch_id']) ? (int) $data['branch_id'] : null,
        ];
    }

    /** @return array{total: int, cold: int, warm: int, hot: int, closing: int, unclassified: int} */
    private function stats(Request $request): array
    {
        $counts = (array) $this->activeLeads($request)
            ->selectRaw("COUNT(*) as total, COALESCE(SUM(CASE WHEN l.current_stage = 'clicked' THEN 1 ELSE 0 END), 0) as cold, COALESCE(SUM(CASE WHEN l.current_stage IN ('asked', 'price_list') THEN 1 ELSE 0 END), 0) as warm, COALESCE(SUM(CASE WHEN l.current_stage IN ('offline', 'online') THEN 1 ELSE 0 END), 0) as hot, COALESCE(SUM(CASE WHEN l.current_stage = 'closing_repeat' THEN 1 ELSE 0 END), 0) as closing, COALESCE(SUM(CASE WHEN l.current_stage IS NULL THEN 1 ELSE 0 END), 0) as unclassified")
            ->first();

        return [
            'total' => (int) ($counts['total'] ?? 0),
            'cold' => (int) ($counts['cold'] ?? 0),
            'warm' => (int) ($counts['warm'] ?? 0),
            'hot' => (int) ($counts['hot'] ?? 0),
            'closing' => (int) ($counts['closing'] ?? 0),
            'unclassified' => (int) ($counts['unclassified'] ?? 0),
        ];
    }

    /** @return array{total: int, unassigned: int, follow_up_due: int, crm_taken_over: int, needs_qualification: int, source_conflict: int} */
    private function priorities(Request $request): array
    {
        $counts = (array) $this->activeLeads($request)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN l.assigned_user_id IS NULL THEN 1 ELSE 0 END), 0) as unassigned, COALESCE(SUM(CASE WHEN l.next_follow_up_at IS NOT NULL AND l.next_follow_up_at <= ? THEN 1 ELSE 0 END), 0) as follow_up_due, COALESCE(SUM(CASE WHEN l.crm_taken_over_at IS NOT NULL THEN 1 ELSE 0 END), 0) as crm_taken_over, COALESCE(SUM(CASE WHEN l.intent_buying IS NULL OR l.usage_date IS NULL OR l.budget IS NULL THEN 1 ELSE 0 END), 0) as needs_qualification, COALESCE(SUM(CASE WHEN l.source_conflict_at IS NOT NULL THEN 1 ELSE 0 END), 0) as source_conflict', [now()])
            ->first();

        return [
            'total' => (int) ($counts['total'] ?? 0),
            'unassigned' => $request->user()->role === 'admin' ? (int) ($counts['unassigned'] ?? 0) : 0,
            'follow_up_due' => (int) ($counts['follow_up_due'] ?? 0),
            'crm_taken_over' => $request->user()->role === 'admin' ? (int) ($counts['crm_taken_over'] ?? 0) : 0,
            'needs_qualification' => (int) ($counts['needs_qualification'] ?? 0),
            'source_conflict' => $request->user()->role === 'admin' ? (int) ($counts['source_conflict'] ?? 0) : 0,
        ];
    }

    /** @return list<\stdClass> */
    private function recentLeads(Request $request): array
    {
        return array_values($this->activeLeads($request)
            ->leftJoin('users as u', 'u.id', '=', 'l.assigned_user_id')
            ->orderByDesc('l.updated_at')
            ->limit(6)
            ->get(['l.id', 'l.name', 'l.current_stage', 'l.next_follow_up_at', 'l.updated_at', 'u.name as owner_name'])
            ->all());
    }

    /** @return array{total: int, complete: int, follow_up_set: int} */
    private function quality(Request $request): array
    {
        $counts = (array) $this->activeLeads($request)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN l.intent_buying IS NOT NULL AND l.usage_date IS NOT NULL AND l.budget IS NOT NULL THEN 1 ELSE 0 END), 0) as complete, COALESCE(SUM(CASE WHEN l.next_follow_up_at IS NOT NULL THEN 1 ELSE 0 END), 0) as follow_up_set')
            ->first();

        return [
            'total' => (int) ($counts['total'] ?? 0),
            'complete' => (int) ($counts['complete'] ?? 0),
            'follow_up_set' => (int) ($counts['follow_up_set'] ?? 0),
        ];
    }

    /** @return list<\stdClass> */
    private function workload(): array
    {
        return array_values(DB::table('branches as b')
            ->leftJoin('users as u', fn ($join) => $join->on('u.branch_id', '=', 'b.id')->where('u.role', 'cs')->where('u.active', true))
            ->leftJoin('leads as l', fn ($join) => $join->on('l.assigned_user_id', '=', 'u.id')->whereNull('l.archived_at'))
            ->groupBy('b.id', 'b.name', 'u.name')
            ->orderBy('b.name')
            ->get(['b.name', 'u.name as cs_name', DB::raw('COUNT(l.id) as lead_count')])
            ->all());
    }

    private function activeLeads(Request $request): Builder
    {
        $query = DB::table('leads as l')->whereNull('l.archived_at');
        $this->restrictToVisibleLeads($query, $request, 'l');

        return $query;
    }

    private function restrictToVisibleLeads(Builder $query, Request $request, string $alias): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        $userId = $request->user()->id;
        $query->where(function (Builder $visible) use ($alias, $userId): void {
            $visible->where("{$alias}.assigned_user_id", $userId)->orWhereExists(function (Builder $collaborator) use ($alias, $userId): void {
                $collaborator->selectRaw('1')
                    ->from('lead_collaborators as c')
                    ->whereColumn('c.lead_id', "{$alias}.id")
                    ->where('c.user_id', $userId)
                    ->whereNull('c.removed_at');
            });
        });
    }

    private function validateLeadFilters(Request $request): void
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'integer', 'exists:branches,id'],
            'stage' => ['nullable', Rule::in(['clicked', 'asked', 'price_list', 'offline', 'online', 'closing_repeat'])],
            'queue' => ['nullable', Rule::in(['unassigned', 'needs_qualification', 'follow_up_due', 'crm_taken_over', 'source_conflict'])],
            'intent' => ['nullable', Rule::in(['Tunangan', 'Lamaran', 'Nikahan', 'Anniversary', 'Lainnya'])],
            'budget' => ['nullable', Rule::in(LeadBudgetRanges::OPTIONS)],
            'lead_date_from' => ['nullable', 'date', 'before_or_equal:lead_date_to'],
            'lead_date_to' => ['nullable', 'date', 'after_or_equal:lead_date_from'],
            'lead_date_status' => ['nullable', Rule::in(['known', 'missing'])],
            'follow_up' => ['nullable', Rule::in(['scheduled', 'none', 'today', 'tomorrow', 'overdue'])],
            'handling' => ['nullable', Rule::in(['primary', 'collaborator'])],
            'crm' => ['nullable', Rule::in(['taken_over', 'not_taken_over'])],
            'archived' => ['nullable', 'boolean'],
        ]);
    }
}
