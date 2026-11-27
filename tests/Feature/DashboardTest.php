<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GoogleSheetSyncService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->string('owner_resolution')->default('manual');
            $table->unsignedBigInteger('source_branch_id')->nullable();
            $table->unsignedBigInteger('source_conflict_branch_id')->nullable();
            $table->timestamp('source_conflict_at')->nullable();
            $table->string('name')->nullable();
            $table->string('imported_cs_name')->nullable();
            $table->string('current_stage')->nullable();
            $table->string('intent_buying')->nullable();
            $table->date('usage_date')->nullable();
            $table->string('budget')->nullable();
            $table->string('outcome')->nullable();
            $table->string('outcome_factor')->nullable();
            $table->text('outcome_note')->nullable();
            $table->timestamp('outcome_recorded_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('crm_taken_over_at')->nullable();
            $table->unsignedBigInteger('revision')->default(0);
            $table->timestamps();
        });

        Schema::create('lead_milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->string('milestone');
            $table->boolean('is_active')->default(false);
        });
        Schema::create('lead_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');
            $table->text('detail')->nullable();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('lead_collaborators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamp('added_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->unique(['lead_id', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('password_hash')->nullable();
            $table->string('role')->default('cs');
            $table->boolean('active')->default(true);
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('default_import_user_id')->nullable();
            $table->string('google_spreadsheet_id')->nullable()->unique();
            $table->timestamp('google_sheets_synced_at')->nullable();
            $table->text('google_sheets_sync_error')->nullable();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('cs_source_name_aliases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');
            $table->string('source_name');
            $table->string('source_name_key');
            $table->timestamp('created_at')->nullable();
            $table->unique(['branch_id', 'source_name_key']);
        });
        Schema::create('lead_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('source');
            $table->string('status');
            $table->json('summary')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
        Schema::create('lead_source_occurrences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('source_branch_id')->nullable();
            $table->string('spreadsheet_id');
            $table->string('source_tab');
            $table->string('source_hash')->unique();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('branch_source_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');
            $table->string('previous_spreadsheet_id')->nullable();
            $table->string('spreadsheet_id')->nullable();
            $table->text('detail')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_branch_page_distinguishes_a_failed_attempt_from_the_last_successful_sync(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $branchId = \DB::table('branches')->insertGetId([
            'name' => 'Palagan',
            'google_spreadsheet_id' => 'sheet-palagan',
            'google_sheets_synced_at' => now()->subMinutes(5),
        ]);
        \DB::table('lead_sync_runs')->insert([
            'branch_id' => $branchId,
            'source' => 'google_sheets',
            'status' => 'failed',
            'error' => 'Tidak menemukan tab harian bernama angka, misalnya 1 sampai 31.',
            'created_at' => now(),
            'completed_at' => now(),
        ]);

        $this->actingAs($admin)->get('/branches')->assertInertia(
            fn (Assert $page) => $page
                ->component('Branches/Index')
                ->where('branches.0.google_sheets_synced_at', fn ($value) => $value !== null)
                ->where('branches.0.last_sync.status', 'failed')
                ->where('branches.0.last_sync.error', 'Tidak menemukan tab harian bernama angka, misalnya 1 sampai 31.'),
        );
    }

    public function test_admin_can_disconnect_a_branch_sheet_without_removing_its_leads(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $branchId = \DB::table('branches')->insertGetId([
            'name' => 'Palagan',
            'google_spreadsheet_id' => 'sheet-palagan',
            'google_sheets_synced_at' => now(),
            'google_sheets_sync_error' => 'Gagal lama',
        ]);
        \DB::table('leads')->insert([
            'source_branch_id' => $branchId,
            'name' => 'Lead tetap ada',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->from('/branches')->delete("/branches/{$branchId}/google-sheet")
            ->assertRedirect('/branches');

        $this->assertDatabaseHas('branches', [
            'id' => $branchId,
            'google_spreadsheet_id' => null,
            'google_sheets_synced_at' => null,
            'google_sheets_sync_error' => null,
        ]);
        $this->assertDatabaseHas('leads', [
            'source_branch_id' => $branchId,
            'name' => 'Lead tetap ada',
        ]);
        $this->assertDatabaseHas('branch_source_events', [
            'branch_id' => $branchId,
            'actor_id' => $admin->id,
            'action' => 'disconnected',
            'previous_spreadsheet_id' => 'sheet-palagan',
        ]);
    }

    public function test_admin_must_use_source_replacement_to_change_a_branch_sheet(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $branchId = \DB::table('branches')->insertGetId([
            'name' => 'Palagan',
            'google_spreadsheet_id' => 'sheet-lama',
        ]);

        $this->actingAs($admin)->patch("/branches/{$branchId}", [
            'name' => 'Palagan',
            'google_spreadsheet_id' => 'sheet-baru',
        ])->assertSessionHasErrors('google_spreadsheet_id');

        $this->assertDatabaseHas('branches', [
            'id' => $branchId,
            'google_spreadsheet_id' => 'sheet-lama',
        ]);
    }

    public function test_admin_can_request_a_branch_source_replacement(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $branchId = \DB::table('branches')->insertGetId([
            'name' => 'Palagan',
            'google_spreadsheet_id' => 'sheet-lama',
        ]);
        $sync = \Mockery::mock(GoogleSheetSyncService::class);
        $sync->shouldReceive('replaceSource')
            ->once()
            ->with($admin->id, $branchId, 'sheet-baru')
            ->andReturn([
                'enriched' => 2,
                'inserted' => 1,
                'skipped' => 0,
                'resurfaced' => 0,
                'unassigned' => 0,
                'needs_review' => 0,
                'missing_dates' => 0,
                'chat_time_review' => 0,
                'missing_source_fields' => 0,
                'failed' => 0,
                'partial' => 0,
            ]);
        $this->app->instance(GoogleSheetSyncService::class, $sync);

        $this->actingAs($admin)->from('/branches')->post("/branches/{$branchId}/replace-google-sheet", [
            'google_spreadsheet_id' => 'sheet-baru',
        ])->assertRedirect('/branches');
    }

    public function test_branch_sync_warns_when_chat_time_needs_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $sync = \Mockery::mock(GoogleSheetSyncService::class);
        $sync->shouldReceive('sync')->once()->with($admin->id, $branchId)->andReturn([
            'inserted' => 0, 'enriched' => 0, 'partial' => 0, 'failed' => 0,
            'missing_source_fields' => 0, 'missing_dates' => 0, 'chat_time_review' => 1,
        ]);
        $this->app->instance(GoogleSheetSyncService::class, $sync);
        $this->actingAs($admin)->from('/branches')->post("/branches/{$branchId}/sync-google-sheet")
            ->assertSessionHas('inertia.flash_data.toast.type', 'warning')
            ->assertSessionHas('inertia.flash_data.toast.message', fn (string $message) => str_contains($message, '1 waktu chat perlu diperiksa'));
    }

    public function test_admin_cannot_reuse_a_historical_sheet_for_a_different_branch(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sourceBranchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $targetBranchId = \DB::table('branches')->insertGetId(['name' => 'Solo']);
        $leadId = \DB::table('leads')->insertGetId([
            'source_branch_id' => $sourceBranchId,
            'name' => 'Lead asal Palagan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('lead_source_occurrences')->insert([
            'lead_id' => $leadId,
            'spreadsheet_id' => 'sheet-palagan-lama',
            'source_tab' => '1',
            'source_hash' => hash('sha256', 'sheet-palagan-lama'),
            'created_at' => now(),
        ]);

        $this->actingAs($admin)->post("/branches/{$targetBranchId}/replace-google-sheet", [
            'google_spreadsheet_id' => 'sheet-palagan-lama',
        ])->assertSessionHasErrors('google_spreadsheet_id');
    }

    public function test_admin_can_preview_a_branch_source_replacement_before_syncing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $branchId = \DB::table('branches')->insertGetId([
            'name' => 'Palagan',
            'google_spreadsheet_id' => 'sheet-lama',
        ]);
        $preview = [
            'rows' => 5,
            'matching' => 3,
            'new' => 1,
            'updates' => 2,
            'conflicts' => 1,
            'missing_source_fields' => 0,
            'failed_tabs' => 0,
        ];
        $sync = \Mockery::mock(GoogleSheetSyncService::class);
        $sync->shouldReceive('previewReplacement')
            ->once()
            ->with($branchId, 'sheet-baru')
            ->andReturn($preview);
        $this->app->instance(GoogleSheetSyncService::class, $sync);

        $this->actingAs($admin)->from('/branches')->post("/branches/{$branchId}/preview-google-sheet", [
            'google_spreadsheet_id' => 'sheet-baru',
        ])->assertRedirect('/branches')
            ->assertSessionHas('source_preview', [...$preview, 'branch_id' => $branchId, 'spreadsheet_id' => 'sheet-baru']);
    }

    public function test_branch_can_reconnect_its_own_sheet_after_a_cross_branch_contact_conflict(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $soloId = \DB::table('branches')->insertGetId(['name' => 'Solo']);
        $leadId = \DB::table('leads')->insertGetId([
            'source_branch_id' => $soloId,
            'name' => 'Lead asal Solo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('lead_source_occurrences')->insert([
            'lead_id' => $leadId,
            'source_branch_id' => $palaganId,
            'spreadsheet_id' => 'sheet-palagan',
            'source_tab' => '1',
            'source_hash' => hash('sha256', 'sheet-palagan'),
            'created_at' => now(),
        ]);
        $sync = \Mockery::mock(GoogleSheetSyncService::class);
        $sync->shouldReceive('replaceSource')
            ->once()
            ->with($admin->id, $palaganId, 'sheet-palagan')
            ->andReturn([
                'enriched' => 0,
                'inserted' => 0,
                'skipped' => 0,
                'resurfaced' => 0,
                'unassigned' => 0,
                'needs_review' => 1,
                'missing_dates' => 0,
                'chat_time_review' => 0,
                'missing_source_fields' => 0,
                'failed' => 0,
                'partial' => 0,
            ]);
        $this->app->instance(GoogleSheetSyncService::class, $sync);

        $this->actingAs($admin)->from('/branches')->post("/branches/{$palaganId}/replace-google-sheet", [
            'google_spreadsheet_id' => 'sheet-palagan',
        ])->assertRedirect('/branches')
            ->assertSessionHasNoErrors();
    }

    public function test_admin_dashboard_prioritizes_work_and_statistics_reports_data_quality(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Pusat']);
        $cs = User::factory()->create();
        $cs->forceFill(['role' => 'cs', 'branch_id' => $branchId])->save();

        \DB::table('leads')->insert([
            [
                'name' => 'Perlu ditindaklanjuti',
                'assigned_user_id' => null,
                'source_branch_id' => null,
                'current_stage' => 'asked',
                'intent_buying' => null,
                'usage_date' => null,
                'budget' => null,
                'next_follow_up_at' => null,
                'updated_at' => now()->subMinute(),
                'created_at' => now()->subMinute(),
            ],
            [
                'name' => 'Lead terbaru',
                'assigned_user_id' => $cs->id,
                'source_branch_id' => $branchId,
                'current_stage' => 'closing_repeat',
                'intent_buying' => 'Nikahan',
                'usage_date' => '2026-12-12',
                'budget' => '10 juta',
                'next_follow_up_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        ]);

        $this->actingAs($admin)->get('/dashboard')->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('priorities.unassigned', 1)
                ->where('priorities.needs_qualification', 1)
                ->where('recent_leads.0.name', 'Lead terbaru'),
        );

        $this->actingAs($admin)->get('/statistics')->assertInertia(
            fn (Assert $page) => $page
                ->component('Statistics')
                ->where('quality.total', 2)
                ->where('quality.complete', 1)
                ->where('quality.needs_qualification', 1)
                ->where('quality.without_follow_up', 2)
                ->where('stats.closing', 1)
                ->where('workload.0.name', 'Pusat')
                ->where('workload.0.lead_count', 1),
        );
    }

    public function test_dashboard_counts_missing_branches_instead_of_missing_owners(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        \DB::table('leads')->insert([
            ['name' => 'Tim cabang', 'source_branch_id' => $branchId, 'assigned_user_id' => null],
            ['name' => 'Tanpa cabang', 'source_branch_id' => null, 'assigned_user_id' => null],
        ]);
        $this->actingAs($admin)->get('/dashboard')->assertInertia(fn (Assert $page) => $page->where('priorities.unassigned', 1));
    }

    public function test_cs_only_receives_its_own_dashboard_data(): void
    {
        $cs = User::factory()->create();
        $cs->forceFill(['role' => 'cs'])->save();
        $otherCs = User::factory()->create();
        $otherCs->forceFill(['role' => 'cs'])->save();

        \DB::table('leads')->insert([
            [
                'name' => 'Lead saya',
                'assigned_user_id' => $cs->id,
                'current_stage' => 'asked',
                'intent_buying' => null,
                'usage_date' => null,
                'budget' => null,
                'next_follow_up_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => 'Lead akun lain',
                'assigned_user_id' => $otherCs->id,
                'current_stage' => 'closing_repeat',
                'intent_buying' => 'Nikahan',
                'usage_date' => '2026-12-12',
                'budget' => '10 juta',
                'next_follow_up_at' => null,
                'updated_at' => now()->subMinute(),
                'created_at' => now()->subMinute(),
            ],
        ]);

        $this->actingAs($cs)->get('/dashboard')->assertInertia(
            fn (Assert $page) => $page
                ->where('priorities.total', 1)
                ->where('recent_leads.0.name', 'Lead saya')
                ->where('priorities.unassigned', 0),
        );

        $this->actingAs($cs)->get('/statistics')->assertInertia(
            fn (Assert $page) => $page
                ->where('quality.total', 1)
                ->where('workload', []),
        );
    }

    public function test_statistics_counts_leads_without_a_stage(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        \DB::table('leads')->insert([
            'name' => 'Belum ditandai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->get('/statistics')->assertInertia(
            fn (Assert $page) => $page
                ->where('stats.total', 1)
                ->where('stats.unclassified', 1),
        );
    }

    public function test_admin_can_filter_statistics_by_source_branch(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $bandungId = \DB::table('branches')->insertGetId(['name' => 'Bandung']);

        \DB::table('leads')->insert([
            [
                'name' => 'Lead Palagan',
                'source_branch_id' => $palaganId,
                'current_stage' => 'asked',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lead Bandung',
                'source_branch_id' => $bandungId,
                'current_stage' => 'clicked',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($admin)->get("/statistics?branch={$palaganId}")->assertInertia(
            fn (Assert $page) => $page
                ->where('stats.total', 1)
                ->where('stats.warm', 1)
                ->where('stats.cold', 0)
                ->where('quality.total', 1)
                ->where('filters.branch', $palaganId)
                ->where('workload.0.name', 'Palagan')
                ->where('workload.0.lead_count', 1)
                ->has('workload', 1),
        );
    }

    public function test_statistics_exposes_active_leads_without_a_source_branch(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        \DB::table('branches')->insert(['name' => 'Palagan']);
        \DB::table('leads')->insert([
            'name' => 'Lead tanpa cabang',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->get('/statistics')->assertInertia(
            fn (Assert $page) => $page
                ->where('stats.total', 1)
                ->where('workload.1.name', 'Tanpa cabang sumber')
                ->where('workload.1.lead_count', 1),
        );
    }

    public function test_statistics_groups_closing_and_loss_factors(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        \DB::table('leads')->insert([
            [
                'name' => 'Deal offline',
                'outcome' => 'closing',
                'outcome_factor' => 'offline_experience',
                'outcome_recorded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tidak ada kabar',
                'outcome' => 'loss',
                'outcome_factor' => 'ghosting',
                'outcome_recorded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($admin)->get('/statistics')->assertInertia(
            fn (Assert $page) => $page
                ->where('outcomes.closing.total', 1)
                ->where('outcomes.closing.factors.9.key', 'offline_experience')
                ->where('outcomes.closing.factors.9.count', 1)
                ->where('outcomes.loss.total', 1)
                ->where('outcomes.loss.factors.8.key', 'ghosting')
                ->where('outcomes.loss.factors.8.count', 1),
        );
    }

    public function test_statistics_keeps_archived_outcomes_and_filters_by_first_result_date(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        \DB::table('leads')->insert([
            [
                'name' => 'Closing diarsipkan',
                'outcome' => 'closing',
                'outcome_factor' => 'offline_experience',
                'outcome_recorded_at' => '2026-11-10 09:00:00',
                'archived_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Loss periode lain',
                'outcome' => 'loss',
                'outcome_factor' => 'ghosting',
                'outcome_recorded_at' => '2026-10-10 09:00:00',
                'archived_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($admin)->get('/statistics?outcome_date_from=2026-11-01&outcome_date_to=2026-11-30')->assertInertia(
            fn (Assert $page) => $page
                ->component('Statistics')
                ->where('outcomes.closing.total', 1)
                ->where('outcomes.closing.factors.9.count', 1)
                ->where('outcomes.closing.factors.9.active_count', 0)
                ->where('outcomes.loss.total', 0)
                ->where('outcomePeriod.from', '2026-11-01')
                ->where('outcomePeriod.to', '2026-11-30'),
        );
    }

    public function test_admin_receives_feedback_after_creating_a_branch(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $branchResponse = $this->actingAs($admin)->post('/branches', ['name' => 'Pusat']);
        $branchResponse->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Cabang ditambahkan.',
        ]);

    }

    public function test_admin_can_add_multiple_active_cs_to_one_branch(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);

        $this->actingAs($admin)->post('/accounts', [
            'name' => 'CS Palagan 1',
            'email' => 'cs-palagan-1@example.test',
            'password' => 'password-yang-aman',
            'role' => 'cs',
            'branch_id' => $branchId,
        ])->assertRedirect();
        $firstCsId = (int) \DB::table('users')->where('email', 'cs-palagan-1@example.test')->value('id');

        $this->actingAs($admin)->post('/accounts', [
            'name' => 'CS Palagan 2',
            'email' => 'cs-palagan-2@example.test',
            'password' => 'password-yang-aman',
            'role' => 'cs',
            'branch_id' => $branchId,
        ])->assertRedirect();

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseHas('branches', [
            'id' => $branchId,
            'default_import_user_id' => $firstCsId,
        ]);

        $this->post(route('logout'));
        $this->post(route('login.store'), [
            'email' => 'cs-palagan-2@example.test',
            'password' => 'password-yang-aman',
        ])->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
    }

    public function test_admin_cannot_move_an_active_cs_to_another_branch_while_they_handle_active_leads(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $bandungId = \DB::table('branches')->insertGetId(['name' => 'Bandung']);
        $cs = User::factory()->create(['name' => 'CS Palagan']);
        $cs->forceFill(['role' => 'cs', 'branch_id' => $palaganId, 'active' => true])->save();
        \DB::table('leads')->insert([
            'assigned_user_id' => $cs->id,
            'name' => 'Lead aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->patch("/accounts/{$cs->id}", [
            'name' => $cs->name,
            'email' => $cs->email,
            'role' => 'cs',
            'branch_id' => $bandungId,
            'active' => true,
        ])->assertSessionHasErrors('branch_id');

        $this->assertDatabaseHas('users', ['id' => $cs->id, 'branch_id' => $palaganId]);
    }

    public function test_branch_index_shows_when_a_sheet_source_is_not_ready_to_import(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        \DB::table('branches')->insert([
            'name' => 'Palagan',
            'google_spreadsheet_id' => 'sheet-palagan',
        ]);

        $this->actingAs($admin)->get('/branches')->assertInertia(
            fn (Assert $page) => $page
                ->component('Branches/Index')
                ->where('branches.0.import_status', 'Nama CS kosong perlu fallback impor'),
        );
    }

    public function test_branch_index_preserves_missing_source_field_count_in_last_sync(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        \DB::table('lead_sync_runs')->insert([
            'branch_id' => $branchId, 'source' => 'google_sheets', 'status' => 'success',
            'summary' => json_encode(['missing_source_fields' => 12], JSON_THROW_ON_ERROR), 'completed_at' => now(),
        ]);
        $this->actingAs($admin)->get('/branches')->assertInertia(fn (Assert $page) => $page
            ->where('branches.0.last_sync.summary.missing_source_fields', 12)
            ->where('branches.0.import_status', 'Nama CS kosong perlu fallback impor'));
    }

    public function test_admin_cannot_delete_a_branch_that_still_has_an_account(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Pusat']);
        $account = User::factory()->create();
        $account->forceFill(['role' => 'cs', 'branch_id' => $branchId])->save();

        $this->actingAs($admin)->delete("/branches/{$branchId}")
            ->assertSessionHasErrors('branch');

        $this->assertDatabaseHas('branches', ['id' => $branchId]);
    }

    public function test_admin_cannot_delete_a_branch_that_is_the_source_of_leads(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        \DB::table('leads')->insert([
            'source_branch_id' => $branchId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->delete("/branches/{$branchId}")
            ->assertSessionHasErrors('branch');

        $this->assertDatabaseHas('branches', ['id' => $branchId]);
    }

    public function test_deactivating_cs_records_why_they_were_removed_from_a_lead(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Pusat']);
        $cs = User::factory()->create(['name' => 'CS Pusat']);
        $cs->forceFill(['role' => 'cs', 'branch_id' => $branchId, 'active' => true])->save();
        $replacement = User::factory()->create(['name' => 'CS Pusat Cadangan']);
        $replacement->forceFill(['role' => 'cs', 'branch_id' => $branchId, 'active' => true])->save();
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $cs->id]);
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $cs->id,
            'name' => 'Lead kerja sama',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('lead_collaborators')->insert([
            'lead_id' => $leadId,
            'user_id' => $cs->id,
            'added_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch("/accounts/{$cs->id}", [
                'name' => 'CS Pusat',
                'email' => $cs->email,
                'role' => 'cs',
                'branch_id' => $branchId,
                'active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'assigned_user_id' => null]);
        $this->assertDatabaseMissing('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $cs->id, 'removed_at' => null]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'action' => 'CS utama dilepas']);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'action' => 'CS pendamping dilepas']);
        $this->assertDatabaseHas('branches', ['id' => $branchId, 'default_import_user_id' => $replacement->id]);
    }
}
