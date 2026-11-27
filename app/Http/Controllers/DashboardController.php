<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetSyncService;
use App\Support\CsName;
use App\Support\LeadBudgetRanges;
use App\Support\LeadOutcomeFactors;
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
        if ($request->input('handling') === 'collaborator') {
            $request->merge(['handling' => 'team']);
        }
        $this->validateLeadFilters($request);
        $filteredCsUserId = $request->filled('cs_user_id')
            ? $request->integer('cs_user_id')
            : null;
        $query = DB::table('leads as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.assigned_user_id')
            ->leftJoin('branches as b', 'b.id', '=', 'u.branch_id')
            ->leftJoin('branches as source_b', 'source_b.id', '=', 'l.source_branch_id')
            ->leftJoin('branches as conflict_b', 'conflict_b.id', '=', 'l.source_conflict_branch_id')
            ->select('l.id', 'l.name', 'l.contact_raw', 'l.phone', 'l.assigned_user_id', 'l.owner_resolution', 'l.imported_cs_name', 'l.source_branch_id', 'l.intent_buying', 'l.usage_date', 'l.budget', 'l.keterangan', 'l.imported_definition', 'l.current_stage', 'l.outcome', 'l.outcome_factor', 'l.outcome_note', 'l.outcome_recorded_at', 'l.next_follow_up_at', 'l.crm_taken_over_at', 'l.revision', 'l.source_conflict_at', 'l.lead_created_on', 'l.chat_time', 'l.created_at', 'l.updated_at', 'u.name as owner_name', DB::raw('COALESCE(b.name, source_b.name) as branch_name'), 'conflict_b.name as source_conflict_branch_name')
            ->orderByRaw('CASE WHEN l.lead_created_on IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('l.lead_created_on')
            ->orderByDesc('l.id');

        $showArchived = $request->boolean('archived');
        abort_if($showArchived && $request->user()->role !== 'admin', 403);
        $query->{$showArchived ? 'whereNotNull' : 'whereNull'}('l.archived_at');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(fn ($builder) => $builder->whereRaw('l.name ILIKE ?', [$term])->orWhereRaw('l.phone ILIKE ?', [$term])->orWhereRaw('l.contact_raw ILIKE ?', [$term]));
        }
        if ($request->user()->role === 'admin' && $request->filled('branch')) {
            $query->where('l.source_branch_id', $request->integer('branch'));
        }
        if ($request->user()->role === 'admin' && $request->boolean('source_branch_missing')) {
            $query->whereNull('l.source_branch_id');
        }
        if ($request->filled('stage')) {
            $query->where('l.current_stage', $request->string('stage')->toString());
        }
        if ($request->filled('outcome')) {
            match ($request->string('outcome')->toString()) {
                'pending' => $query->whereNull('l.outcome'),
                default => $query->where('l.outcome', $request->string('outcome')->toString()),
            };
        }
        if ($request->filled('outcome_factor')) {
            $query->where('l.outcome_factor', $request->string('outcome_factor')->toString());
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
            $branchId = (int) ($request->user()->branch_id ?? 0);
            match ($request->string('handling')->toString()) {
                'primary' => $query->where('l.assigned_user_id', $userId),
                'collaborator' => $query->whereExists(function (Builder $collaborator) use ($userId): void {
                    $collaborator->selectRaw('1')
                        ->from('lead_collaborators as c')
                        ->whereColumn('c.lead_id', 'l.id')
                        ->where('c.user_id', $userId)
                        ->whereNull('c.removed_at');
                }),
                'team' => $query->where('l.source_branch_id', $branchId)
                    ->where(fn (Builder $owner) => $owner->whereNull('l.assigned_user_id')->orWhere('l.assigned_user_id', '!=', $userId)),
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
        if ($filteredCsUserId !== null) {
            /** @var object{id: int, name: string, branch_id: int|null}|null $account */
            $account = DB::table('users')->where('id', $filteredCsUserId)->first(['id', 'name', 'branch_id']);
            abort_if($account === null, 404);
            $keys = $this->sourceNameKeysForAccount($account);
            $sourceNames = DB::table('leads')->where('source_branch_id', $account->branch_id)
                ->whereNotNull('imported_cs_name')->distinct()->pluck('imported_cs_name')
                ->filter(fn (string $name): bool => in_array(CsName::key($name), $keys, true))->values()->all();
            $query->where(function (Builder $source) use ($account, $sourceNames, $filteredCsUserId): void {
                $source->where(fn (Builder $named) => $named->where('l.source_branch_id', $account->branch_id)->whereIn('l.imported_cs_name', $sourceNames))
                    ->orWhere(fn (Builder $fallback) => $fallback->where('l.assigned_user_id', $filteredCsUserId)
                        ->where(fn (Builder $blank) => $blank->whereNull('l.imported_cs_name')->orWhere('l.imported_cs_name', '')));
            });
        }
        if ($request->filled('queue')) {
            match ($request->string('queue')->toString()) {
                'unassigned' => $request->user()->role === 'admin' ? $query->whereNull('l.assigned_user_id') : null,
                'awaiting_account' => $request->user()->role === 'admin' ? $query->where('l.owner_resolution', 'awaiting_account') : null,
                'needs_qualification' => $query->where(fn (Builder $builder) => $builder->whereNull('l.intent_buying')->orWhereNull('l.usage_date')->orWhereNull('l.budget')),
                'follow_up_due' => $query->whereNotNull('l.next_follow_up_at')->where('l.next_follow_up_at', '<=', now()),
                'crm_taken_over' => $query->whereNotNull('l.crm_taken_over_at'),
                'source_conflict' => $request->user()->role === 'admin' ? $query->whereNotNull('l.source_conflict_at') : null,
                default => null,
            };
        }
        $this->restrictToVisibleLeads($query, $request, 'l');

        $leads = $query->get();
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
        $csAccounts = DB::table('users as u')
            ->join('branches as b', 'b.id', '=', 'u.branch_id')
            ->where('u.role', 'cs')
            ->where('u.active', true)
            ->when($request->user()->role === 'cs', fn (Builder $builder) => $builder->where('u.branch_id', $request->user()->branch_id))
            ->orderBy('b.name')
            ->get(['u.id', 'u.name', 'u.branch_id', 'b.name as branch_name']);
        $sourceNameOwners = [];
        foreach ($csAccounts as $account) {
            $sourceNameOwners[$account->branch_id.'|'.CsName::key((string) $account->name)][$account->id] = true;
        }
        foreach (DB::table('cs_source_name_aliases as a')->join('users as u', 'u.id', '=', 'a.user_id')
            ->whereIn('u.id', $csAccounts->pluck('id'))->whereColumn('a.branch_id', 'u.branch_id')->get(['a.user_id', 'a.branch_id', 'a.source_name_key']) as $alias) {
            $sourceNameOwners[$alias->branch_id.'|'.CsName::key((string) $alias->source_name_key)][$alias->user_id] = true;
        }
        $blankSourceIds = $leads->filter(fn (object $lead): bool => trim((string) $lead->imported_cs_name) === '')->pluck('id');
        $importedBlankIds = array_fill_keys(DB::table('leads')->whereIn('id', $blankSourceIds)->whereNotNull('import_fingerprint')->pluck('id')->all(), true);
        $blankOrigins = DB::table('lead_activity')->whereIn('lead_id', $blankSourceIds)->whereIn('action', ['Lead dibuat manual', 'Diimpor dari tab harian', 'Diimpor dari CSV/XLSX'])->get(['lead_id', 'action'])->groupBy('lead_id');
        $currentUserId = (int) $request->user()->id;
        foreach ($leads as $lead) {
            $lead->milestones = $milestonesByLead[$lead->id] ?? [];
            $lead->collaborators = $collaboratorsByLead[$lead->id] ?? [];
            $lead->source_cs_account_status = null;
            $sourceName = trim((string) $lead->imported_cs_name);
            if (! $showArchived && $sourceName === '') {
                $actions = $blankOrigins->get($lead->id, collect())->pluck('action')->all();
                if (! in_array('Lead dibuat manual', $actions, true) && (isset($importedBlankIds[$lead->id]) || $lead->owner_resolution === 'fallback' || in_array('Diimpor dari tab harian', $actions, true) || in_array('Diimpor dari CSV/XLSX', $actions, true))) {
                    $lead->source_cs_account_status = 'source_empty';
                }
            } elseif (! $showArchived && $lead->source_branch_id === null) {
                $lead->source_cs_account_status = 'branch_missing';
            } elseif (! $showArchived) {
                $matches = count($sourceNameOwners[$lead->source_branch_id.'|'.CsName::key((string) $lead->imported_cs_name)] ?? []);
                $lead->source_cs_account_status = match ($matches) {
                    0 => 'missing',
                    1 => $lead->owner_resolution === 'awaiting_account' && $lead->assigned_user_id === null ? 'awaiting_link' : 'matched',
                    default => 'ambiguous',
                };
            }
            $lead->cs_filter_match = $filteredCsUserId === null
                ? null
                : (trim((string) $lead->imported_cs_name) !== '' ? 'CS sumber' : 'CS utama');
            $activity = $latestActivityByLead[$lead->id] ?? null;
            $lead->latest_activity = $activity->action ?? null;
            $lead->latest_activity_detail = $activity->detail ?? null;
            $lead->latest_activity_at = $activity->created_at ?? null;
            $lead->handling_role = $request->user()->role === 'admin'
                ? 'admin'
                : ((int) $lead->assigned_user_id === $currentUserId
                    ? 'primary'
                    : (in_array($currentUserId, array_column($lead->collaborators, 'id'), true)
                        ? 'collaborator'
                        : 'team'));
        }

        $sheetSync = $request->user()->role === 'admin'
            ? DB::table('lead_sync_runs')->where('source', 'google_sheets')->whereNull('branch_id')->orderByDesc('completed_at')->first(['status', 'completed_at', 'summary'])
            : null;
        if ($sheetSync && $sheetSync->summary) {
            $sheetSync->summary = json_decode((string) $sheetSync->summary, true) ?: null;
        }

        return Inertia::render('Leads/Index', [
            'leads' => [
                'data' => $leads,
                'total' => $leads->count(),
            ],
            'filters' => $request->only('q', 'branch', 'source_branch_missing', 'stage', 'outcome', 'outcome_factor', 'queue', 'intent', 'budget', 'lead_date_from', 'lead_date_to', 'lead_date_status', 'follow_up', 'handling', 'crm', 'cs_user_id', 'archived'),
            'archiveMode' => $showArchived,
            'branches' => $request->user()->role === 'admin' ? DB::table('branches')->orderBy('name')->get(['id', 'name']) : [],
            'csAccounts' => $csAccounts,
            'outcomeFactors' => LeadOutcomeFactors::all(),
            'sheetSync' => $sheetSync,
        ]);
    }

    public function statistics(Request $request): Response
    {
        $this->validateStatisticsFilters($request);

        return Inertia::render('Statistics', [
            'stats' => $this->stats($request),
            'quality' => $this->quality($request),
            'outcomes' => $this->outcomes($request),
            'branches' => $request->user()->role === 'admin'
                ? DB::table('branches')->orderBy('name')->get(['id', 'name'])
                : [],
            'filters' => [
                'branch' => $request->filled('branch') ? $request->integer('branch') : null,
            ],
            'outcomePeriod' => [
                'from' => $request->string('outcome_date_from')->toString() ?: null,
                'to' => $request->string('outcome_date_to')->toString() ?: null,
            ],
            'workload' => $request->user()->role === 'admin' ? $this->workload($request) : [],
        ]);
    }

    public function branches(Request $request): Response
    {
        $csByBranch = DB::table('users')
            ->where('role', 'cs')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'branch_id', 'name', 'email'])
            ->groupBy('branch_id');
        $lastRunsByBranch = [];
        foreach (DB::table('lead_sync_runs')
            ->where('source', 'google_sheets')
            ->whereNotNull('branch_id')
            ->orderByDesc('completed_at')
            ->get(['branch_id', 'status', 'summary', 'error', 'completed_at']) as $run) {
            /** @var object{branch_id: int, status: string, summary: string|null, error: string|null, completed_at: string|null} $run */
            $branchId = (int) $run->branch_id;
            if (! isset($lastRunsByBranch[$branchId])) {
                $lastRunsByBranch[$branchId] = $run;
            }
        }
        $sourceEventsByBranch = DB::table('branch_source_events as event')
            ->leftJoin('users as actor', 'actor.id', '=', 'event.actor_id')
            ->orderByDesc('event.created_at')
            ->get(['event.id', 'event.branch_id', 'event.action', 'event.previous_spreadsheet_id', 'event.spreadsheet_id', 'event.detail', 'event.created_at', 'actor.name as actor_name'])
            ->groupBy('branch_id');
        $branches = DB::table('branches')
            ->orderBy('name')
            ->get(['id', 'name', 'default_import_user_id', 'google_spreadsheet_id', 'google_sheets_synced_at', 'google_sheets_sync_error'])
            ->map(function (object $branch) use ($csByBranch, $lastRunsByBranch, $sourceEventsByBranch): object {
                $branch->cs_accounts = array_values($csByBranch->get($branch->id, collect())->all());
                $hasDefaultImportCs = collect($branch->cs_accounts)->contains(
                    fn (object $account): bool => (int) data_get($account, 'id') === (int) $branch->default_import_user_id,
                );
                $branch->import_status = match (true) {
                    ! $branch->google_spreadsheet_id => 'Sumber Google Sheets belum diatur',
                    ! $hasDefaultImportCs => 'Nama CS kosong perlu fallback impor',
                    default => 'Siap impor',
                };
                $lastRun = $lastRunsByBranch[(int) $branch->id] ?? null;
                $lastRunSummary = is_string($lastRun?->summary)
                    ? json_decode($lastRun->summary, true)
                    : null;
                $branch->last_sync = $lastRun
                    ? [
                        'status' => $lastRun->status,
                        'completed_at' => $lastRun->completed_at,
                        'summary' => is_array($lastRunSummary) ? $lastRunSummary : null,
                        'error' => $lastRun->error,
                    ]
                    : null;
                $branch->source_events = $sourceEventsByBranch
                    ->get($branch->id, collect())
                    ->take(5)
                    ->values()
                    ->all();

                return $branch;
            });

        return Inertia::render('Branches/Index', [
            'branches' => $branches,
            'service_account_email' => $this->serviceAccountEmail(),
            'source_preview' => $request->session()->get('source_preview'),
            'reconciliation_preview' => $request->session()->get('reconciliation_preview'),
        ]);
    }

    private function serviceAccountEmail(): ?string
    {
        $path = config('leads.google_sheets.credentials_path');
        if (! is_string($path) || $path === '' || ! is_file($path) || ! is_readable($path)) {
            return null;
        }
        $contents = @file_get_contents($path);
        $credentials = is_string($contents) ? json_decode($contents, true) : null;
        $email = is_array($credentials) ? ($credentials['client_email'] ?? null) : null;

        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public function accounts(): Response
    {
        $accounts = DB::table('users as u')->leftJoin('branches as b', 'b.id', '=', 'u.branch_id')->orderByDesc('u.active')->orderBy('u.name')->get(['u.id', 'u.name', 'u.email', 'u.role', 'u.branch_id', 'u.active', 'b.name as branch_name']);
        $aliasesByUser = DB::table('cs_source_name_aliases')->orderBy('source_name')->get(['user_id', 'source_name', 'source_name_key'])->groupBy('user_id');
        $sourceNameOwners = [];
        foreach ($accounts as $account) {
            $account->source_name_aliases = $aliasesByUser->get($account->id, collect())->pluck('source_name')->values()->all();
            if ($account->role !== 'cs' || ! $account->active || ! $account->branch_id) {
                continue;
            }
            $keys = collect([CsName::key((string) $account->name)])
                ->concat($aliasesByUser->get($account->id, collect())->pluck('source_name_key')->map(fn (string $key): string => CsName::key($key)))
                ->unique();
            foreach ($keys as $key) {
                $sourceNameOwners[(int) $account->branch_id.'|'.$key][(int) $account->id] = true;
            }
        }
        $awaitingByAccount = [];
        $reconcilableByAccount = [];
        $legacyCounts = DB::table('lead_collaborators')->whereNull('removed_at')->select('user_id')->selectRaw('COUNT(*) as total')->groupBy('user_id')->pluck('total', 'user_id');
        $unmatchedSourceNames = [];
        foreach (DB::table('leads')->whereNotNull('source_branch_id')->whereNotNull('imported_cs_name')->whereNull('archived_at')->get(['assigned_user_id', 'owner_resolution', 'source_branch_id', 'imported_cs_name']) as $lead) {
            $sourceKey = (int) $lead->source_branch_id.'|'.CsName::key((string) $lead->imported_cs_name);
            $owners = array_keys($sourceNameOwners[$sourceKey] ?? []);
            if (count($owners) !== 1) {
                if (trim((string) $lead->imported_cs_name) !== '') {
                    $unmatchedSourceNames[$sourceKey] ??= ['name' => $lead->imported_cs_name, 'branch_id' => (int) $lead->source_branch_id, 'count' => 0, 'ambiguous' => count($owners) > 1];
                    $unmatchedSourceNames[$sourceKey]['count']++;
                }

                continue;
            }
            $accountId = (int) $owners[0];
            if ($lead->owner_resolution === 'awaiting_account' && $lead->assigned_user_id === null) {
                $awaitingByAccount[$accountId] = ($awaitingByAccount[$accountId] ?? 0) + 1;
            } elseif ($lead->owner_resolution !== 'source_matched' || (int) $lead->assigned_user_id !== $accountId) {
                $reconcilableByAccount[$accountId] = ($reconcilableByAccount[$accountId] ?? 0) + 1;
            }
        }
        $accounts->each(function (object $account) use ($awaitingByAccount, $reconcilableByAccount, $legacyCounts): void {
            $account->legacy_collaborator_count = (int) ($legacyCounts[$account->id] ?? 0);
            $account->awaiting_lead_count = $account->role === 'cs' && $account->active
                ? ($awaitingByAccount[$account->id] ?? 0)
                : 0;
            $account->reconcilable_lead_count = $account->role === 'cs' && $account->active
                ? ($reconcilableByAccount[$account->id] ?? 0)
                : 0;
        });

        $branches = DB::table('branches')->orderBy('name')->get(['id', 'name']);
        $branchNames = $branches->pluck('name', 'id');
        $unmatchedSourceNames = array_values(array_map(static fn (array $entry): array => [...$entry, 'branch_name' => $branchNames->get($entry['branch_id'], 'Cabang tidak diketahui')], $unmatchedSourceNames));

        return Inertia::render('Accounts/Index', ['accounts' => $accounts, 'branches' => $branches, 'unmatched_source_names' => $unmatchedSourceNames]);
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $data = $this->branchData($request);
        $this->assertSpreadsheetBelongsToBranch($data['google_spreadsheet_id'], null);
        $branchId = DB::table('branches')->insertGetId(['name' => $data['name'], 'google_spreadsheet_id' => $data['google_spreadsheet_id'], 'created_at' => now()]);
        if ($data['google_spreadsheet_id']) {
            $this->recordSourceEvent($branchId, $request->user()->id, 'connected', null, $data['google_spreadsheet_id'], 'Spreadsheet sumber terhubung saat cabang dibuat.');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cabang ditambahkan.']);

        return back();
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $data = $this->accountData($request, null, true);
        if ($data['password'] === null) {
            throw ValidationException::withMessages(['password' => 'Kata sandi wajib diisi.']);
        }
        DB::transaction(function () use ($data): void {
            $accountId = DB::table('users')->insertGetId(['name' => $data['name'], 'email' => $data['email'], 'password_hash' => bcrypt($data['password']), 'role' => $data['role'], 'branch_id' => $data['role'] === 'cs' ? $data['branch_id'] : null, 'created_at' => now()]);
            if ($data['role'] === 'cs') {
                $this->assertCsSourceNamesAvailable($data['branch_id'], $accountId, [$data['name'], ...$data['source_name_aliases']]);
                $this->syncCsSourceNameAliases($accountId, $data['branch_id'], $data['name'], $data['source_name_aliases']);
            }
            $this->ensureBranchDefaultImportCs($data['role'] === 'cs' ? $data['branch_id'] : null);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Akun ditambahkan.']);

        return back();
    }

    public function updateBranch(Request $request, int $branch): RedirectResponse
    {
        $record = DB::table('branches')->where('id', $branch)->first() ?? abort(404);
        $data = $this->branchData($request, $record->id);
        if ($data['google_spreadsheet_id'] !== $record->google_spreadsheet_id) {
            throw ValidationException::withMessages(['google_spreadsheet_id' => 'Gunakan tindakan Ganti spreadsheet sumber agar data lead tetap terlindungi.']);
        }
        DB::table('branches')->where('id', $branch)->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cabang diperbarui.']);

        return back();
    }

    public function replaceBranchGoogleSheet(Request $request, GoogleSheetSyncService $sync, int $branch): RedirectResponse
    {
        $record = DB::table('branches')->where('id', $branch)->first() ?? abort(404);
        $data = $request->validate([
            'google_spreadsheet_id' => ['required', 'string', 'max:128', Rule::unique('branches', 'google_spreadsheet_id')->ignore($record->id)],
        ]);
        $spreadsheetId = trim((string) $data['google_spreadsheet_id']);
        if ($spreadsheetId === '') {
            throw ValidationException::withMessages(['google_spreadsheet_id' => 'ID Google Spreadsheet wajib diisi.']);
        }
        $this->assertSpreadsheetBelongsToBranch($spreadsheetId, $record->id);

        try {
            $summary = $sync->replaceSource($request->user()->id, $record->id, $spreadsheetId);
        } catch (\RuntimeException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        $message = "Spreadsheet sumber diperbarui: {$summary['inserted']} lead baru, {$summary['enriched']} data sumber diperbarui. Data kerja CS tetap dipertahankan.";
        if ($summary['chat_time_review'] > 0) {
            $message .= " {$summary['chat_time_review']} waktu chat perlu diperiksa melalui Periksa data historis.";
        }
        Inertia::flash('toast', ['type' => $summary['partial'] || $summary['chat_time_review'] > 0 ? 'warning' : 'success', 'message' => $message]);

        return back();
    }

    public function previewBranchGoogleSheet(Request $request, GoogleSheetSyncService $sync, int $branch): RedirectResponse
    {
        $record = DB::table('branches')->where('id', $branch)->first() ?? abort(404);
        $data = $request->validate([
            'google_spreadsheet_id' => ['required', 'string', 'max:128', Rule::unique('branches', 'google_spreadsheet_id')->ignore($record->id)],
        ]);
        $spreadsheetId = trim((string) $data['google_spreadsheet_id']);
        if ($spreadsheetId === '') {
            throw ValidationException::withMessages(['google_spreadsheet_id' => 'ID Google Spreadsheet wajib diisi.']);
        }
        $this->assertSpreadsheetBelongsToBranch($spreadsheetId, $record->id);

        try {
            $preview = $sync->previewReplacement($record->id, $spreadsheetId);
        } catch (\RuntimeException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        return back()->with('source_preview', [...$preview, 'branch_id' => $record->id, 'spreadsheet_id' => $spreadsheetId]);
    }

    public function previewBranchReconciliation(Request $request, GoogleSheetSyncService $sync, int $branch): RedirectResponse
    {
        abort_unless(DB::table('branches')->where('id', $branch)->exists(), 404);
        $request->session()->forget('reconciliation_preview');
        try {
            $plan = $sync->previewReconciliation($branch);
        } catch (\RuntimeException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }
        $request->session()->put('reconciliation_preview', [
            ...$plan, 'branch_id' => $branch,
            'hash' => hash('sha256', json_encode($plan, JSON_THROW_ON_ERROR)),
        ]);

        return back();
    }

    public function applyBranchReconciliation(Request $request, GoogleSheetSyncService $sync, int $branch): RedirectResponse
    {
        $request->validate(['backup_confirmed' => ['accepted'], 'period_confirmed' => ['accepted']]);
        $preview = $request->session()->get('reconciliation_preview');
        if (! is_array($preview) || ($preview['branch_id'] ?? null) !== $branch || ! is_string($preview['hash'] ?? null)) {
            throw ValidationException::withMessages(['reconciliation' => 'Buat pratinjau baru sebelum menerapkan koreksi.']);
        }
        try {
            $updated = $sync->applyReconciliation($branch, (int) $request->user()->id, $preview['hash']);
        } catch (\RuntimeException $exception) {
            $request->session()->forget('reconciliation_preview');
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }
        $request->session()->forget('reconciliation_preview');
        Inertia::flash('toast', ['type' => 'success', 'message' => "{$updated} data sumber berhasil direkonsiliasi. Data kerja dan penugasan CS tetap dipertahankan."]);

        return back();
    }

    public function archiveMissingBranchLeads(Request $request, GoogleSheetSyncService $sync, int $branch): RedirectResponse
    {
        $request->validate(['backup_confirmed' => ['accepted'], 'period_confirmed' => ['accepted'], 'source_coverage_confirmed' => ['accepted']]);
        $preview = $request->session()->get('reconciliation_preview');
        if (! is_array($preview) || ($preview['branch_id'] ?? null) !== $branch || ! is_string($preview['hash'] ?? null)) {
            throw ValidationException::withMessages(['reconciliation' => 'Buat pratinjau baru sebelum mengarsipkan lead.']);
        }
        try {
            $archived = $sync->archiveMissing($branch, (int) $request->user()->id, $preview['hash']);
        } catch (\RuntimeException $exception) {
            $request->session()->forget('reconciliation_preview');
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }
        $request->session()->forget('reconciliation_preview');
        Inertia::flash('toast', ['type' => 'success', 'message' => "{$archived} lead impor diarsipkan. Lead dapat dipulihkan dari arsip."]);

        return back();
    }

    private function assertSpreadsheetBelongsToBranch(?string $spreadsheetId, ?int $branchId): void
    {
        if (! $spreadsheetId) {
            return;
        }

        $sourceBranch = DB::table('branch_source_events as event')
            ->join('branches as branch', 'branch.id', '=', 'event.branch_id')
            ->where('event.spreadsheet_id', $spreadsheetId)
            ->whereIn('event.action', ['connected', 'replaced'])
            ->when($branchId, fn ($query) => $query->where('event.branch_id', '!=', $branchId))
            ->orderBy('branch.name')
            ->value('branch.name');
        $sourceBranch ??= DB::table('lead_source_occurrences as occurrence')
            ->join('branches as branch', 'branch.id', '=', 'occurrence.source_branch_id')
            ->where('occurrence.spreadsheet_id', $spreadsheetId)
            ->when($branchId, fn ($query) => $query->where('occurrence.source_branch_id', '!=', $branchId))
            ->orderBy('branch.name')
            ->value('branch.name');
        $sourceBranch ??= DB::table('lead_source_occurrences as occurrence')
            ->join('leads as lead', 'lead.id', '=', 'occurrence.lead_id')
            ->join('branches as branch', 'branch.id', '=', 'lead.source_branch_id')
            ->where('occurrence.spreadsheet_id', $spreadsheetId)
            ->whereNull('occurrence.source_branch_id')
            ->when($branchId, fn ($query) => $query->where('lead.source_branch_id', '!=', $branchId))
            ->orderBy('branch.name')
            ->value('branch.name');
        if ($sourceBranch) {
            throw ValidationException::withMessages(['google_spreadsheet_id' => "Spreadsheet ini memiliki riwayat sumber lead di cabang {$sourceBranch}. Gunakan alur pindah sumber khusus agar atribusi lead tetap tepat."]);
        }
    }

    public function disconnectBranchGoogleSheet(Request $request, GoogleSheetSyncService $sync, int $branch): RedirectResponse
    {
        abort_unless(DB::table('branches')->where('id', $branch)->exists(), 404);

        try {
            $sync->disconnect($request->user()->id, $branch);
        } catch (\RuntimeException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Koneksi Google Sheets diputus. Lead dan riwayat yang sudah tersimpan tetap ada.']);

        return back();
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

    /** @return array{name: string, google_spreadsheet_id: string|null, default_import_user_id: int|null} */
    private function branchData(Request $request, ?int $branchId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('branches', 'name')->ignore($branchId)],
            'google_spreadsheet_id' => ['nullable', 'string', 'max:128', Rule::unique('branches', 'google_spreadsheet_id')->ignore($branchId)],
            'default_import_user_id' => ['nullable', 'integer'],
        ]);
        $defaultImportUserId = isset($data['default_import_user_id']) ? (int) $data['default_import_user_id'] : null;
        if ($branchId && $defaultImportUserId && ! DB::table('users')->where('id', $defaultImportUserId)->where('branch_id', $branchId)->where('role', 'cs')->where('active', true)->exists()) {
            throw ValidationException::withMessages(['default_import_user_id' => 'Pilih akun CS aktif dari cabang ini.']);
        }
        if ($branchId && ! $defaultImportUserId && DB::table('users')->where('branch_id', $branchId)->where('role', 'cs')->where('active', true)->exists()) {
            throw ValidationException::withMessages(['default_import_user_id' => 'Pilih CS fallback impor untuk cabang ini.']);
        }

        return ['name' => (string) $data['name'], 'google_spreadsheet_id' => trim((string) ($data['google_spreadsheet_id'] ?? '')) ?: null, 'default_import_user_id' => $defaultImportUserId];
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

        $message = "Sinkronisasi cabang selesai: {$summary['inserted']} lead baru, {$summary['enriched']} data sumber diperbarui.";
        if ($summary['partial']) {
            $message .= ' Sebagian tab perlu diperbaiki; data dari tab valid tetap masuk.';
        }
        if ($summary['missing_source_fields']) {
            $message .= " {$summary['missing_source_fields']} kolom sumber kosong perlu dilengkapi di Google Sheets.";
        }
        if ($summary['missing_dates']) {
            $message .= " {$summary['missing_dates']} lead baru tanpa tanggal; periksa bulan dan tahun pada judul spreadsheet serta nomor tab.";
        }
        if ($summary['chat_time_review'] > 0) {
            $message .= " {$summary['chat_time_review']} waktu chat perlu diperiksa melalui Periksa data historis.";
        }
        Inertia::flash('toast', ['type' => $summary['failed'] ? 'error' : ($summary['partial'] || $summary['chat_time_review'] > 0 ? 'warning' : 'success'), 'message' => $summary['failed'] ? 'Sinkronisasi cabang gagal. Periksa konfigurasi atau akun CS.' : $message]);

        return back();
    }

    public function updateAccount(Request $request, int $account): RedirectResponse
    {
        $record = DB::table('users')->where('id', $account)->first() ?? abort(404);
        $active = $request->boolean('active', (bool) $record->active);
        $data = $this->accountData($request, $record->id);
        if ($record->id === $request->user()->id && ! $active) {
            throw ValidationException::withMessages(['active' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }
        if ($record->role === 'admin' && $record->active && ($data['role'] !== 'admin' || ! $active) && DB::table('users')->where('role', 'admin')->where('active', true)->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'Minimal satu admin aktif harus tersedia.']);
        }
        $isMovingActiveCs = $record->role === 'cs'
            && $data['role'] === 'cs'
            && $active
            && (int) $record->branch_id !== $data['branch_id'];
        if ($isMovingActiveCs && DB::table('leads')->where('assigned_user_id', $record->id)->whereNull('archived_at')->exists()) {
            throw ValidationException::withMessages(['branch_id' => 'Pindahkan penugasan CS utama pada lead aktif sebelum memindahkan cabang.']);
        }
        if ($data['role'] === 'cs') {
            $this->assertCsSourceNamesAvailable($data['branch_id'], $record->id, [$data['name'], ...$data['source_name_aliases']]);
        }

        DB::transaction(function () use ($record, $data, $active, $request, $isMovingActiveCs): void {
            if ($isMovingActiveCs) {
                $legacyLeadIds = DB::table('lead_collaborators')->where('user_id', $record->id)->whereNull('removed_at')->lockForUpdate()->pluck('lead_id');
                if ($legacyLeadIds->isNotEmpty() && ! $request->boolean('release_historical_confirmed')) {
                    throw ValidationException::withMessages(['release_historical_confirmed' => 'Konfirmasi pengakhiran hak pendamping historis sebelum pindah cabang.']);
                }
                if ($legacyLeadIds->isNotEmpty()) {
                    $now = now();
                    DB::table('lead_collaborators')->where('user_id', $record->id)->whereNull('removed_at')->update(['removed_at' => $now]);
                    DB::table('leads')->whereIn('id', $legacyLeadIds)->update(['updated_at' => $now, 'revision' => DB::raw('revision + 1')]);
                    DB::table('lead_activity')->insert($legacyLeadIds->map(fn ($leadId): array => [
                        'lead_id' => $leadId, 'actor_id' => $request->user()->id,
                        'action' => 'Hak pendamping historis diakhiri', 'detail' => "{$record->name} pindah cabang; riwayat penugasan dipertahankan.", 'created_at' => $now,
                    ])->all());
                }
            }
            $updates = ['name' => $data['name'], 'email' => $data['email'], 'role' => $data['role'], 'branch_id' => $data['role'] === 'cs' ? $data['branch_id'] : null, 'active' => $active];
            if ($data['password']) {
                $updates['password_hash'] = bcrypt($data['password']);
            }
            DB::table('users')->where('id', $record->id)->update($updates);
            if ((! $active || $data['role'] !== 'cs') && $record->role === 'cs') {
                $now = now();
                $primaryLeadIds = DB::table('leads')->where('assigned_user_id', $record->id)->pluck('id')->all();
                $collaboratorLeadIds = DB::table('lead_collaborators')->where('user_id', $record->id)->whereNull('removed_at')->pluck('lead_id')->all();
                DB::table('leads')->whereIn('id', $primaryLeadIds)->update(['assigned_user_id' => null, 'owner_resolution' => 'unassigned', 'updated_at' => $now, 'revision' => DB::raw('revision + 1')]);
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
            if ($data['role'] === 'cs') {
                $this->syncCsSourceNameAliases($record->id, $data['branch_id'], $data['name'], $data['source_name_aliases']);
            } else {
                DB::table('cs_source_name_aliases')->where('user_id', $record->id)->delete();
            }
            $this->ensureBranchDefaultImportCs($record->branch_id ? (int) $record->branch_id : null);
            $this->ensureBranchDefaultImportCs($data['role'] === 'cs' ? $data['branch_id'] : null);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => $active ? 'Akun diperbarui.' : 'Akun dinonaktifkan dan lead terkait dilepas.']);

        return back();
    }

    public function linkAwaitingLeadsToAccount(Request $request, int $account): RedirectResponse
    {
        /** @var object{id: int, name: string, branch_id: int|null} $record */
        $record = DB::table('users')->where('id', $account)->where('role', 'cs')->where('active', true)->first(['id', 'name', 'branch_id']) ?? abort(404);
        $sourceNameKeys = $this->sourceNameKeysForAccount($record);
        $this->assertSourceNameKeysUnambiguous($record, $sourceNameKeys);
        $leads = DB::transaction(function () use ($record, $sourceNameKeys, $request) {
            $leads = DB::table('leads')
                ->where('source_branch_id', $record->branch_id)
                ->where('owner_resolution', 'awaiting_account')
                ->whereNull('assigned_user_id')
                ->whereNull('archived_at')
                ->lockForUpdate()
                ->get(['id', 'imported_cs_name'])
                ->filter(fn (object $lead): bool => in_array(CsName::key((string) $lead->imported_cs_name), $sourceNameKeys, true))
                ->values();
            if ($leads->isEmpty()) {
                return $leads;
            }

            $ids = $leads->pluck('id')->all();
            $now = now();
            DB::table('leads')->whereIn('id', $ids)->where('owner_resolution', 'awaiting_account')->whereNull('assigned_user_id')->update([
                'assigned_user_id' => $record->id,
                'owner_resolution' => 'source_matched',
                'updated_at' => $now,
                'revision' => DB::raw('revision + 1'),
            ]);
            DB::table('lead_collaborators')->whereIn('lead_id', $ids)->where('user_id', $record->id)->whereNull('removed_at')->update(['removed_at' => $now]);
            DB::table('lead_activity')->insert($leads->map(fn (object $lead): array => [
                'lead_id' => $lead->id,
                'actor_id' => $request->user()->id,
                'action' => 'CS utama dihubungkan dari Nama CS sumber',
                'detail' => "Nama CS sumber {$lead->imported_cs_name} dihubungkan ke akun {$record->name}. CS pendamping tidak diubah.",
                'created_at' => $now,
            ])->all());

            return $leads;
        });
        if ($leads->isEmpty()) {
            Inertia::flash('toast', ['type' => 'info', 'message' => 'Tidak ada lead menunggu yang cocok dengan nama akun ini.']);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$leads->count()} lead dihubungkan ke {$record->name}."]);

        return back();
    }

    public function reconcileSourceLeadsToAccount(Request $request, int $account): RedirectResponse
    {
        /** @var object{id: int, name: string, branch_id: int|null} $record */
        $record = DB::table('users')->where('id', $account)->where('role', 'cs')->where('active', true)->first(['id', 'name', 'branch_id']) ?? abort(404);
        $sourceNameKeys = $this->sourceNameKeysForAccount($record);
        $this->assertSourceNameKeysUnambiguous($record, $sourceNameKeys);
        $leads = DB::transaction(function () use ($record, $sourceNameKeys, $request) {
            $leads = DB::table('leads')
                ->where('source_branch_id', $record->branch_id)
                ->whereNotNull('imported_cs_name')
                ->whereNull('archived_at')
                ->lockForUpdate()
                ->get(['id', 'assigned_user_id', 'owner_resolution', 'imported_cs_name'])
                ->filter(fn (object $lead): bool => in_array(CsName::key((string) $lead->imported_cs_name), $sourceNameKeys, true))
                ->reject(fn (object $lead): bool => $lead->owner_resolution === 'awaiting_account' && $lead->assigned_user_id === null)
                ->reject(fn (object $lead): bool => $lead->owner_resolution === 'source_matched' && (int) $lead->assigned_user_id === (int) $record->id)
                ->values();
            if ($leads->isEmpty()) {
                return $leads;
            }

            $ids = $leads->pluck('id')->all();
            $now = now();
            DB::table('leads')->whereIn('id', $ids)->update([
                'assigned_user_id' => $record->id,
                'owner_resolution' => 'source_matched',
                'updated_at' => $now,
                'revision' => DB::raw('revision + 1'),
            ]);
            DB::table('lead_collaborators')->whereIn('lead_id', $ids)->where('user_id', $record->id)->whereNull('removed_at')->update(['removed_at' => $now]);
            DB::table('lead_activity')->insert($leads->map(fn (object $lead): array => [
                'lead_id' => $lead->id,
                'actor_id' => $request->user()->id,
                'action' => 'CS utama direkonsiliasi dari Nama CS sumber',
                'detail' => "Nama CS sumber {$lead->imported_cs_name} ditetapkan ke akun {$record->name}. CS pendamping tidak diubah.",
                'created_at' => $now,
            ])->all());

            return $leads;
        });
        if ($leads->isEmpty()) {
            Inertia::flash('toast', ['type' => 'info', 'message' => 'Tidak ada lead historis yang perlu direkonsiliasi.']);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$leads->count()} lead historis direkonsiliasi ke {$record->name}."]);

        return back();
    }

    /**
     * @return array{name: string, email: string, password: string|null, role: 'admin'|'cs', branch_id: int|null, source_name_aliases: list<string>}
     */
    private function accountData(Request $request, ?int $accountId = null, bool $passwordRequired = false): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($accountId)],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'min:12'],
            'role' => ['required', 'in:admin,cs'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'source_name_aliases' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($data['role'] === 'cs') {
            if (! ($data['branch_id'] ?? false)) {
                throw ValidationException::withMessages(['branch_id' => 'Cabang wajib dipilih untuk akun CS.']);
            }
        }

        return [
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => isset($data['password']) ? (string) $data['password'] : null,
            'role' => $data['role'] === 'admin' ? 'admin' : 'cs',
            'branch_id' => isset($data['branch_id']) ? (int) $data['branch_id'] : null,
            'source_name_aliases' => $data['role'] === 'cs' ? $this->sourceNameAliases((string) ($data['source_name_aliases'] ?? '')) : [],
        ];
    }

    /** @return list<string> */
    private function sourceNameAliases(string $value): array
    {
        $aliases = [];
        foreach (preg_split('/[\r\n,]+/', $value) ?: [] as $alias) {
            $alias = trim($alias);
            if ($alias !== '') {
                $aliases[CsName::key($alias)] = $alias;
            }
        }

        return array_values($aliases);
    }

    /** @param list<string> $sourceNames */
    private function assertCsSourceNamesAvailable(?int $branchId, int $accountId, array $sourceNames): void
    {
        $keys = array_values(array_unique(array_map(fn (string $name): string => CsName::key($name), $sourceNames)));
        $activeAccountConflict = DB::table('users')
            ->where('branch_id', $branchId)
            ->where('role', 'cs')
            ->where('active', true)
            ->where('id', '!=', $accountId)
            ->get(['name'])
            ->contains(fn (object $user): bool => in_array(CsName::key((string) $user->name), $keys, true));
        $aliasConflict = DB::table('cs_source_name_aliases')
            ->where('branch_id', $branchId)
            ->where('user_id', '!=', $accountId)
            ->whereIn('source_name_key', $keys)
            ->exists();
        if ($activeAccountConflict || $aliasConflict) {
            throw ValidationException::withMessages(['source_name_aliases' => 'Nama sumber atau alias sudah dipakai akun CS lain pada cabang ini.']);
        }
    }

    /** @param list<string> $aliases */
    private function syncCsSourceNameAliases(int $accountId, ?int $branchId, string $accountName, array $aliases): void
    {
        DB::table('cs_source_name_aliases')->where('user_id', $accountId)->delete();
        $accountNameKey = CsName::key($accountName);
        $rows = collect($aliases)
            ->filter(fn (string $alias): bool => CsName::key($alias) !== $accountNameKey)
            ->map(fn (string $alias): array => [
                'branch_id' => $branchId,
                'user_id' => $accountId,
                'source_name' => $alias,
                'source_name_key' => CsName::key($alias),
                'created_at' => now(),
            ])->all();
        if ($rows !== []) {
            DB::table('cs_source_name_aliases')->insert($rows);
        }
    }

    /**
     * @param  object{id: int, name: string, branch_id: int|null}  $account
     * @return list<string>
     */
    private function sourceNameKeysForAccount(object $account): array
    {
        return array_values(collect([CsName::key((string) $account->name)])
            ->concat(DB::table('cs_source_name_aliases')->where('user_id', $account->id)->where('branch_id', $account->branch_id)->pluck('source_name_key')->map(fn (string $key): string => CsName::key($key)))
            ->unique()
            ->all());
    }

    /**
     * @param  object{id: int, name: string, branch_id: int|null}  $account
     * @param  list<string>  $sourceNameKeys
     */
    private function assertSourceNameKeysUnambiguous(object $account, array $sourceNameKeys): void
    {
        $owners = [];
        foreach (DB::table('users')->where('role', 'cs')->where('active', true)->where('branch_id', $account->branch_id)->get(['id', 'name']) as $user) {
            $owners[CsName::key((string) $user->name)][(int) $user->id] = true;
        }
        foreach (DB::table('cs_source_name_aliases as alias')->join('users as user', 'user.id', '=', 'alias.user_id')->where('alias.branch_id', $account->branch_id)->where('user.role', 'cs')->where('user.active', true)->get(['alias.user_id', 'alias.source_name_key']) as $alias) {
            $owners[CsName::key((string) $alias->source_name_key)][(int) $alias->user_id] = true;
        }
        foreach ($sourceNameKeys as $key) {
            if (array_keys($owners[$key] ?? []) !== [(int) $account->id]) {
                throw ValidationException::withMessages(['account' => 'Nama akun CS atau aliasnya ambigu pada cabang yang sama. Perbaiki pemetaan nama sumber terlebih dahulu.']);
            }
        }
    }

    private function ensureBranchDefaultImportCs(?int $branchId): void
    {
        if (! $branchId) {
            return;
        }

        $hasValidDefault = DB::table('branches as b')
            ->join('users as u', 'u.id', '=', 'b.default_import_user_id')
            ->where('b.id', $branchId)
            ->whereColumn('u.branch_id', 'b.id')
            ->where('u.role', 'cs')
            ->where('u.active', true)
            ->exists();
        if ($hasValidDefault) {
            return;
        }

        $nextDefault = DB::table('users')
            ->where('branch_id', $branchId)
            ->where('role', 'cs')
            ->where('active', true)
            ->orderBy('created_at')
            ->orderBy('id')
            ->value('id');
        DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $nextDefault]);
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
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN l.source_branch_id IS NULL THEN 1 ELSE 0 END), 0) as unassigned, COALESCE(SUM(CASE WHEN l.next_follow_up_at IS NOT NULL AND l.next_follow_up_at <= ? THEN 1 ELSE 0 END), 0) as follow_up_due, COALESCE(SUM(CASE WHEN l.crm_taken_over_at IS NOT NULL THEN 1 ELSE 0 END), 0) as crm_taken_over, COALESCE(SUM(CASE WHEN l.intent_buying IS NULL OR l.usage_date IS NULL OR l.budget IS NULL THEN 1 ELSE 0 END), 0) as needs_qualification, COALESCE(SUM(CASE WHEN l.source_conflict_at IS NOT NULL THEN 1 ELSE 0 END), 0) as source_conflict', [now()])
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

    /** @return array{total: int, complete: int, follow_up_set: int, needs_qualification: int, without_follow_up: int} */
    private function quality(Request $request): array
    {
        $counts = (array) $this->activeLeads($request)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN l.intent_buying IS NOT NULL AND l.usage_date IS NOT NULL AND l.budget IS NOT NULL THEN 1 ELSE 0 END), 0) as complete, COALESCE(SUM(CASE WHEN l.next_follow_up_at IS NOT NULL THEN 1 ELSE 0 END), 0) as follow_up_set, COALESCE(SUM(CASE WHEN l.intent_buying IS NULL OR l.usage_date IS NULL OR l.budget IS NULL THEN 1 ELSE 0 END), 0) as needs_qualification, COALESCE(SUM(CASE WHEN l.next_follow_up_at IS NULL THEN 1 ELSE 0 END), 0) as without_follow_up')
            ->first();

        return [
            'total' => (int) ($counts['total'] ?? 0),
            'complete' => (int) ($counts['complete'] ?? 0),
            'follow_up_set' => (int) ($counts['follow_up_set'] ?? 0),
            'needs_qualification' => (int) ($counts['needs_qualification'] ?? 0),
            'without_follow_up' => (int) ($counts['without_follow_up'] ?? 0),
        ];
    }

    /** @return array{closing: array{total: int, factors: list<array{key: string, label: string, count: int, active_count: int}>}, loss: array{total: int, factors: list<array{key: string, label: string, count: int, active_count: int}>}} */
    private function outcomes(Request $request): array
    {
        $counts = $this->outcomeLeads($request)
            ->whereNotNull('l.outcome')
            ->whereNotNull('l.outcome_factor')
            ->groupBy('l.outcome', 'l.outcome_factor')
            ->selectRaw("l.outcome || ':' || l.outcome_factor as outcome_factor_key, COUNT(*) as total")
            ->pluck('total', 'outcome_factor_key')
            ->all();
        $activeCounts = $this->outcomeLeads($request)
            ->whereNull('l.archived_at')
            ->whereNotNull('l.outcome')
            ->whereNotNull('l.outcome_factor')
            ->groupBy('l.outcome', 'l.outcome_factor')
            ->selectRaw("l.outcome || ':' || l.outcome_factor as outcome_factor_key, COUNT(*) as total")
            ->pluck('total', 'outcome_factor_key')
            ->all();

        $report = [];
        foreach (['closing', 'loss'] as $outcome) {
            $factors = [];
            $total = 0;
            foreach (LeadOutcomeFactors::forOutcome($outcome) as $key => $label) {
                $count = (int) ($counts["{$outcome}:{$key}"] ?? 0);
                $total += $count;
                $factors[] = [
                    'key' => $key,
                    'label' => $label,
                    'count' => $count,
                    'active_count' => (int) ($activeCounts["{$outcome}:{$key}"] ?? 0),
                ];
            }
            $report[$outcome] = ['total' => $total, 'factors' => $factors];
        }

        /** @var array{closing: array{total: int, factors: list<array{key: string, label: string, count: int, active_count: int}>}, loss: array{total: int, factors: list<array{key: string, label: string, count: int, active_count: int}>}} $report */
        return $report;
    }

    /** @return list<\stdClass> */
    private function workload(Request $request): array
    {
        $query = DB::table('branches as b')
            ->leftJoin('leads as l', fn ($join) => $join->on('l.source_branch_id', '=', 'b.id')->whereNull('l.archived_at'))
            ->groupBy('b.id', 'b.name')
            ->orderBy('b.name')
            ->select('b.id', 'b.name', DB::raw('COUNT(l.id) as lead_count'));

        if ($request->filled('branch')) {
            $query->where('b.id', $request->integer('branch'));
        }

        $workload = $query->get()->all();
        if (! $request->filled('branch')) {
            $withoutSourceBranch = DB::table('leads')
                ->whereNull('archived_at')
                ->whereNull('source_branch_id')
                ->count();
            if ($withoutSourceBranch > 0) {
                $workload[] = (object) [
                    'id' => null,
                    'name' => 'Tanpa cabang sumber',
                    'lead_count' => $withoutSourceBranch,
                    'is_unassigned_source_branch' => true,
                ];
            }
        }

        return array_values($workload);
    }

    private function activeLeads(Request $request): Builder
    {
        return $this->visibleLeads($request)->whereNull('l.archived_at');
    }

    private function outcomeLeads(Request $request): Builder
    {
        $query = $this->visibleLeads($request);
        if ($request->filled('outcome_date_from')) {
            $query->whereDate('l.outcome_recorded_at', '>=', $request->string('outcome_date_from')->toString());
        }
        if ($request->filled('outcome_date_to')) {
            $query->whereDate('l.outcome_recorded_at', '<=', $request->string('outcome_date_to')->toString());
        }

        return $query;
    }

    private function visibleLeads(Request $request): Builder
    {
        $query = DB::table('leads as l');
        $this->restrictToVisibleLeads($query, $request, 'l');

        return $query;
    }

    private function restrictToVisibleLeads(Builder $query, Request $request, string $alias): void
    {
        if ($request->user()->role === 'admin') {
            if ($request->filled('branch')) {
                $query->where("{$alias}.source_branch_id", $request->integer('branch'));
            }

            return;
        }

        $userId = $request->user()->id;
        $branchId = (int) ($request->user()->branch_id ?? 0);
        $query->where(fn (Builder $source) => $source->where("{$alias}.source_branch_id", $branchId)
            ->orWhere(fn (Builder $unknown) => $unknown->whereNull("{$alias}.source_branch_id")->where("{$alias}.assigned_user_id", $userId)));
        $query->where(function (Builder $visible) use ($alias, $userId, $branchId): void {
            $visible->where("{$alias}.assigned_user_id", $userId)->orWhereExists(function (Builder $collaborator) use ($alias, $userId): void {
                $collaborator->selectRaw('1')
                    ->from('lead_collaborators as c')
                    ->whereColumn('c.lead_id', "{$alias}.id")
                    ->where('c.user_id', $userId)
                    ->whereNull('c.removed_at');
            });
            if ($branchId > 0) {
                $visible->orWhere("{$alias}.source_branch_id", $branchId);
            }
        });
    }

    private function validateLeadFilters(Request $request): void
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'integer', 'exists:branches,id'],
            'source_branch_missing' => ['nullable', 'boolean'],
            'stage' => ['nullable', Rule::in(['clicked', 'asked', 'price_list', 'offline', 'online', 'closing_repeat'])],
            'outcome' => ['nullable', Rule::in(['closing', 'loss', 'pending'])],
            'outcome_factor' => ['nullable', Rule::in(array_keys(LeadOutcomeFactors::CLOSING + LeadOutcomeFactors::LOSS))],
            'queue' => ['nullable', Rule::in(['unassigned', 'awaiting_account', 'needs_qualification', 'follow_up_due', 'crm_taken_over', 'source_conflict'])],
            'intent' => ['nullable', Rule::in(['Tunangan', 'Lamaran', 'Nikahan', 'Anniversary', 'Lainnya'])],
            'budget' => ['nullable', Rule::in(LeadBudgetRanges::OPTIONS)],
            'lead_date_from' => ['nullable', 'date', 'before_or_equal:lead_date_to'],
            'lead_date_to' => ['nullable', 'date', 'after_or_equal:lead_date_from'],
            'lead_date_status' => ['nullable', Rule::in(['known', 'missing'])],
            'follow_up' => ['nullable', Rule::in(['scheduled', 'none', 'today', 'tomorrow', 'overdue'])],
            'handling' => ['nullable', Rule::in(['primary', 'collaborator', 'team'])],
            'crm' => ['nullable', Rule::in(['taken_over', 'not_taken_over'])],
            'cs_user_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'cs')->where('active', true)],
            'archived' => ['nullable', 'boolean'],
        ]);
    }

    private function validateStatisticsFilters(Request $request): void
    {
        $request->validate([
            'branch' => ['nullable', 'integer', 'exists:branches,id'],
            'outcome_date_from' => ['nullable', 'date', 'before_or_equal:outcome_date_to'],
            'outcome_date_to' => ['nullable', 'date', 'after_or_equal:outcome_date_from'],
        ]);
    }
}
