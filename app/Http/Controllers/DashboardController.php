<?php

namespace App\Http\Controllers;

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
        $query = DB::table('leads as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.assigned_user_id')
            ->leftJoin('branches as b', 'b.id', '=', 'u.branch_id')
            ->select('l.id', 'l.name', 'l.contact_raw', 'l.phone', 'l.intent_buying', 'l.imported_definition', 'l.current_stage', 'l.next_follow_up_at', 'l.updated_at', 'u.name as owner_name', 'b.name as branch_name')
            ->orderByDesc('l.updated_at');

        $showArchived = $request->boolean('archived');
        $query->{$showArchived ? 'whereNotNull' : 'whereNull'}('l.archived_at');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(fn ($builder) => $builder->whereRaw('l.name ILIKE ?', [$term])->orWhereRaw('l.phone ILIKE ?', [$term])->orWhereRaw('l.contact_raw ILIKE ?', [$term]));
        }
        if ($request->user()->role === 'admin' && $request->filled('branch')) {
            $query->where('u.branch_id', $request->integer('branch'));
        }
        if ($request->filled('stage')) {
            $query->where('l.current_stage', $request->string('stage')->toString());
        }
        if ($request->filled('queue')) {
            match ($request->string('queue')->toString()) {
                'unassigned' => $request->user()->role === 'admin' ? $query->whereNull('l.assigned_user_id') : null,
                'needs_qualification' => $query->where(fn (Builder $builder) => $builder->whereNull('l.intent_buying')->orWhereNull('l.usage_date')->orWhereNull('l.budget')),
                'follow_up_due' => $query->whereNotNull('l.next_follow_up_at')->where('l.next_follow_up_at', '<=', now()),
                default => null,
            };
        }
        if ($request->user()->role !== 'admin') {
            $query->where('l.assigned_user_id', $request->user()->id);
        }

        return Inertia::render('Leads/Index', [
            'leads' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only('q', 'branch', 'stage', 'queue', 'archived'),
            'archiveMode' => $showArchived,
            'branches' => $request->user()->role === 'admin' ? DB::table('branches')->orderBy('name')->get(['id', 'name']) : [],
            'csAccounts' => $request->user()->role === 'admin'
                ? DB::table('users as u')->join('branches as b', 'b.id', '=', 'u.branch_id')->where('u.role', 'cs')->where('u.active', true)->orderBy('b.name')->get(['u.id', 'u.name', 'b.name as branch_name'])
                : [],
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
        return Inertia::render('Branches/Index', ['branches' => DB::table('branches as b')->leftJoin('users as u', fn ($join) => $join->on('u.branch_id', '=', 'b.id')->where('u.role', 'cs')->where('u.active', true))->orderBy('b.name')->get(['b.id', 'b.name', 'u.name as cs_name', 'u.email as cs_email'])]);
    }

    public function accounts(): Response
    {
        return Inertia::render('Accounts/Index', ['accounts' => DB::table('users as u')->leftJoin('branches as b', 'b.id', '=', 'u.branch_id')->orderByDesc('u.active')->orderBy('u.name')->get(['u.id', 'u.name', 'u.email', 'u.role', 'u.branch_id', 'u.active', 'b.name as branch_name']), 'branches' => DB::table('branches')->orderBy('name')->get(['id', 'name'])]);
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:branches,name']]);
        DB::table('branches')->insert(['name' => $data['name'], 'created_at' => now()]);

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
        $data = $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('branches', 'name')->ignore($record->id)]]);
        DB::table('branches')->where('id', $branch)->update(['name' => $data['name']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cabang diperbarui.']);

        return back();
    }

    public function destroyBranch(int $branch): RedirectResponse
    {
        $record = DB::table('branches')->where('id', $branch)->first() ?? abort(404);
        if (DB::table('users')->where('branch_id', $record->id)->exists()) {
            throw ValidationException::withMessages(['branch' => 'Cabang masih memiliki akun. Pindahkan akun terlebih dahulu.']);
        }
        DB::table('branches')->where('id', $record->id)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cabang dihapus.']);

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

        DB::transaction(function () use ($record, $data, $active): void {
            $updates = ['name' => $data['name'], 'email' => $data['email'], 'role' => $data['role'], 'branch_id' => $data['role'] === 'cs' ? $data['branch_id'] : null, 'active' => $active];
            if ($data['password']) {
                $updates['password_hash'] = bcrypt($data['password']);
            }
            DB::table('users')->where('id', $record->id)->update($updates);
            if (! $active && $record->active) {
                DB::table('leads')->where('assigned_user_id', $record->id)->update(['assigned_user_id' => null, 'updated_at' => now()]);
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
            ->selectRaw("COUNT(*) as total, COALESCE(SUM(CASE WHEN l.current_stage = 'clicked' THEN 1 ELSE 0 END), 0) as cold, COALESCE(SUM(CASE WHEN l.current_stage IN ('asked', 'price_list') THEN 1 ELSE 0 END), 0) as warm, COALESCE(SUM(CASE WHEN l.current_stage IN ('offline', 'online', 'closing_pending_transfer') THEN 1 ELSE 0 END), 0) as hot, COALESCE(SUM(CASE WHEN l.current_stage IN ('closing', 'repeat_order') THEN 1 ELSE 0 END), 0) as closing, COALESCE(SUM(CASE WHEN l.current_stage IS NULL THEN 1 ELSE 0 END), 0) as unclassified")
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

    /** @return array{total: int, unassigned: int, follow_up_due: int, needs_qualification: int} */
    private function priorities(Request $request): array
    {
        $counts = (array) $this->activeLeads($request)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN l.assigned_user_id IS NULL THEN 1 ELSE 0 END), 0) as unassigned, COALESCE(SUM(CASE WHEN l.next_follow_up_at IS NOT NULL AND l.next_follow_up_at <= ? THEN 1 ELSE 0 END), 0) as follow_up_due, COALESCE(SUM(CASE WHEN l.intent_buying IS NULL OR l.usage_date IS NULL OR l.budget IS NULL THEN 1 ELSE 0 END), 0) as needs_qualification', [now()])
            ->first();

        return [
            'total' => (int) ($counts['total'] ?? 0),
            'unassigned' => $request->user()->role === 'admin' ? (int) ($counts['unassigned'] ?? 0) : 0,
            'follow_up_due' => (int) ($counts['follow_up_due'] ?? 0),
            'needs_qualification' => (int) ($counts['needs_qualification'] ?? 0),
        ];
    }

    /** @return list<object{id: int, name: string|null, current_stage: string|null, next_follow_up_at: string|null, updated_at: string|null, owner_name: string|null}> */
    private function recentLeads(Request $request): array
    {
        return $this->activeLeads($request)
            ->leftJoin('users as u', 'u.id', '=', 'l.assigned_user_id')
            ->orderByDesc('l.updated_at')
            ->limit(6)
            ->get(['l.id', 'l.name', 'l.current_stage', 'l.next_follow_up_at', 'l.updated_at', 'u.name as owner_name'])
            ->all();
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

    /** @return list<object{name: string, cs_name: string|null, lead_count: int}> */
    private function workload(): array
    {
        return DB::table('branches as b')
            ->leftJoin('users as u', fn ($join) => $join->on('u.branch_id', '=', 'b.id')->where('u.role', 'cs')->where('u.active', true))
            ->leftJoin('leads as l', fn ($join) => $join->on('l.assigned_user_id', '=', 'u.id')->whereNull('l.archived_at'))
            ->groupBy('b.id', 'b.name', 'u.name')
            ->orderBy('b.name')
            ->get(['b.name', 'u.name as cs_name', DB::raw('COUNT(l.id) as lead_count')])
            ->all();
    }

    private function activeLeads(Request $request): Builder
    {
        $query = DB::table('leads as l')->whereNull('l.archived_at');
        if ($request->user()->role !== 'admin') {
            $query->where('l.assigned_user_id', $request->user()->id);
        }

        return $query;
    }
}
