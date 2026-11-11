<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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
            $table->timestamp('archived_at')->nullable();
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
