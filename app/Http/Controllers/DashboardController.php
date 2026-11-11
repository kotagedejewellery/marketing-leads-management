<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function dashboard(Request $request): Response
    {
        return Inertia::render('Dashboard', ['stats' => $this->stats($request)]);
    }

    public function leads(Request $request): Response
    {
        $query = DB::table('leads as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.assigned_user_id')
            ->leftJoin('branches as b', 'b.id', '=', 'u.branch_id')
            ->select('l.id', 'l.name', 'l.contact_raw', 'l.phone', 'l.intent_buying', 'l.imported_definition', 'l.current_stage', 'l.next_follow_up_at', 'l.updated_at', 'u.name as owner_name', 'b.name as branch_name')
            ->orderByDesc('l.updated_at');

        $showArchived = $request->user()->role === 'admin' && $request->boolean('archived');
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
        if ($request->user()->role === 'admin' && $request->filled('queue')) {
            match ($request->string('queue')->toString()) {
                'unassigned' => $query->whereNull('l.assigned_user_id'),
                'needs_qualification' => $query->whereNull('l.intent_buying')->whereNull('l.usage_date')->whereNull('l.budget'),
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
        return Inertia::render('Statistics', ['stats' => $this->stats($request)]);
    }

    public function branches(): Response
    {
        return Inertia::render('Branches/Index', ['branches' => DB::table('branches as b')->leftJoin('users as u', fn ($join) => $join->on('u.branch_id', '=', 'b.id')->where('u.role', 'cs')->where('u.active', true))->orderBy('b.name')->get(['b.id', 'b.name', 'u.name as cs_name', 'u.email as cs_email'])]);
    }

    public function accounts(): Response
    {
        return Inertia::render('Accounts/Index', ['accounts' => DB::table('users as u')->leftJoin('branches as b', 'b.id', '=', 'u.branch_id')->orderByDesc('u.active')->orderBy('u.name')->get(['u.id', 'u.name', 'u.email', 'u.role', 'u.branch_id', 'u.active', 'b.name as branch_name']), 'branches' => DB::table('branches')->orderBy('name')->get(['id', 'name'])]);
    }

    public function storeBranch(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:branches,name']]);
        DB::table('branches')->insert(['name' => $data['name'], 'created_at' => now()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cabang ditambahkan.']);

        return back();
    }

    public function storeAccount(Request $request)
    {
        $data = $this->accountData($request, null, true);
        DB::table('users')->insert(['name' => $data['name'], 'email' => $data['email'], 'password_hash' => bcrypt($data['password']), 'role' => $data['role'], 'branch_id' => $data['role'] === 'cs' ? $data['branch_id'] : null, 'created_at' => now()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Akun ditambahkan.']);

        return back();
    }

    public function updateBranch(Request $request, int $branch)
    {
        $record = DB::table('branches')->where('id', $branch)->first() ?? abort(404);
        $data = $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('branches', 'name')->ignore($record->id)]]);
        DB::table('branches')->where('id', $branch)->update(['name' => $data['name']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cabang diperbarui.']);

        return back();
    }

    public function destroyBranch(int $branch)
    {
        $record = DB::table('branches')->where('id', $branch)->first() ?? abort(404);
        if (DB::table('users')->where('branch_id', $record->id)->exists()) {
            throw ValidationException::withMessages(['branch' => 'Cabang masih memiliki akun. Pindahkan akun terlebih dahulu.']);
        }
        DB::table('branches')->where('id', $record->id)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cabang dihapus.']);

        return back();
    }

    public function updateAccount(Request $request, int $account)
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

        return $data;
    }

    private function stats(Request $request): array
    {
        $query = DB::table('leads as l')->whereNull('l.archived_at');
        if ($request->user()->role !== 'admin') {
            $query->where('l.assigned_user_id', $request->user()->id);
        }

        return [
            'total' => (clone $query)->count(),
            'cold' => (clone $query)->where('current_stage', 'clicked')->count(),
            'warm' => (clone $query)->whereIn('current_stage', ['asked', 'price_list'])->count(),
            'hot' => (clone $query)->whereIn('current_stage', ['offline', 'online', 'closing_pending_transfer'])->count(),
            'closing' => (clone $query)->whereIn('current_stage', ['closing', 'repeat_order'])->count(),
            'unassigned' => $request->user()->role === 'admin' ? (clone $query)->whereNull('l.assigned_user_id')->count() : 0,
            'follow_up_due' => (clone $query)->whereNotNull('l.next_follow_up_at')->where('l.next_follow_up_at', '<=', now())->count(),
        ];
    }
}
