<?php

namespace Tests\Feature;

use App\Models\User;
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
            $table->string('name')->nullable();
            $table->string('current_stage')->nullable();
            $table->string('intent_buying')->nullable();
            $table->date('usage_date')->nullable();
            $table->string('budget')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lead_milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->string('milestone');
            $table->boolean('is_active')->default(false);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('password_hash')->nullable();
            $table->string('role')->default('cs');
            $table->boolean('active')->default(true);
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
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
                'current_stage' => 'closing',
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
                ->where('workload.0.name', 'Pusat')
                ->where('workload.0.lead_count', 1),
        );
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
                'current_stage' => 'closing',
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

    public function test_admin_receives_feedback_after_creating_a_branch()
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $branchResponse = $this->actingAs($admin)->post('/branches', ['name' => 'Pusat']);
        $branchResponse->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Cabang ditambahkan.',
        ]);

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
}
