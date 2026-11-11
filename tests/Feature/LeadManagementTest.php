<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('password_hash')->nullable();
            $table->string('role')->default('cs');
            $table->boolean('active')->default(true);
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->string('import_fingerprint')->nullable()->unique();
            $table->string('contact_key')->nullable()->unique();
            $table->string('source_row_no')->nullable();
            $table->string('source_date_raw')->nullable();
            $table->text('contact_raw')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('respond_time')->nullable();
            $table->string('imported_cs_name')->nullable();
            $table->text('imported_keterangan')->nullable();
            $table->text('raw_kebutuhan')->nullable();
            $table->text('greeting')->nullable();
            $table->string('chat_time')->nullable();
            $table->string('source_description')->nullable();
            $table->string('campaign_description')->nullable();
            $table->string('imported_definition')->nullable();
            $table->string('intent_buying')->nullable();
            $table->date('usage_date')->nullable();
            $table->string('budget')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('current_stage')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
        Schema::create('lead_milestones', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_id');
            $table->string('milestone');
            $table->boolean('is_active')->default(true);
            $table->timestamp('marked_at')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->unique(['lead_id', 'milestone']);
        });
        Schema::create('lead_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');
            $table->text('detail')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_admin_imports_a_phone_once_and_maps_its_first_milestone(): void
    {
        $admin = $this->user('admin');
        $csv = implode("\n", [
            'No,Nama/Nomor,Respond Time,Nama CS,Keterangan,Kebutuhan,Greeting*,Waktu Chat,Keterangan Sumber*,Keterangan Campaign*,Definisi Leads',
            '1,+62 851-8772-5089,0,nanda,sampai pricelist,nikah,halo,10.38,Iklan,Bio,Warm',
        ]);

        $response = $this->actingAs($admin)->post('/leads/import', ['csv_file' => UploadedFile::fake()->createWithContent('leads.csv', $csv)]);
        $response->assertRedirect('/leads');
        $response->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Import selesai: 1 baru, 0 duplikat dilewati, 1 perlu ditugaskan, 0 perlu review.',
        ]);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285187725089', 'current_stage' => 'price_list']);
        $this->assertDatabaseHas('lead_milestones', ['milestone' => 'price_list', 'is_active' => true]);

        $this->actingAs($admin)->post('/leads/import', ['csv_file' => UploadedFile::fake()->createWithContent('leads.csv', $csv)]);
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_admin_imports_the_all_leads_header_and_derives_its_classification(): void
    {
        $admin = $this->user('admin');
        $csv = implode("\n", [
            'Tanggal,Nama/Nomor,Respond Time,Nama CS,Keterangan,Kebutuhan,Greeting*,Waktu Chat,Keterangan Sumber*,Keterangan Campaign*',
            '31,+62 851-7777-1111,0,Dila,sampai pl banding,nikah 10jt,halo,10.38,Iklan,Bio',
        ]);

        $response = $this->actingAs($admin)->post('/leads/import', ['csv_file' => UploadedFile::fake()->createWithContent('all-leads.csv', $csv)]);

        $response->assertRedirect('/leads');
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771111',
            'source_date_raw' => '31',
            'imported_definition' => 'Warm',
            'imported_keterangan' => 'sampai pl banding',
        ]);
        $this->assertDatabaseHas('lead_milestones', ['milestone' => 'price_list', 'is_active' => true]);
    }

    public function test_admin_can_create_a_lead_with_optional_source_data(): void
    {
        $admin = $this->user('admin');

        $response = $this->actingAs($admin)->post('/leads', [
            'contact_raw' => '+62 851-7777-2222',
            'source_date_raw' => '31',
            'imported_keterangan' => 'tanya-tanya',
            'raw_kebutuhan' => 'lamaran',
            'source_description' => 'Instagram',
            'campaign_description' => 'DM',
            'milestone' => 'asked',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177772222',
            'source_date_raw' => '31',
            'imported_definition' => 'Warm',
            'source_description' => 'Instagram',
        ]);
        $this->assertDatabaseHas('lead_milestones', ['milestone' => 'asked', 'is_active' => true]);
    }

    public function test_admin_can_import_the_all_leads_shape_from_xlsx(): void
    {
        $admin = $this->user('admin');
        $path = tempnam(sys_get_temp_dir(), 'all-leads-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['Tanggal', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting*', 'Waktu Chat', 'Keterangan Sumber*', 'Keterangan Campaign*']));
        $writer->addRow(Row::fromValues(['31', '+62 851-7777-3333', '0', 'Dila', 'klik saja', '', 'halo', '10.38', 'Iklan', 'Bio']));
        $writer->close();

        $response = $this->actingAs($admin)->post('/leads/import', ['csv_file' => new UploadedFile($path, 'all-leads.XLSX', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true)]);

        $response->assertRedirect('/leads');
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177773333', 'source_date_raw' => '31', 'imported_definition' => 'Cold']);
        @unlink($path);
    }

    public function test_cs_cannot_open_a_lead_owned_by_another_cs(): void
    {
        $owner = $this->user('cs');
        $viewer = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId(['assigned_user_id' => $owner->id, 'contact_raw' => '+6285100000000', 'contact_key' => 'phone:6285100000000', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($viewer)->get("/leads/{$leadId}")->assertNotFound();
    }

    public function test_admin_can_assign_an_unassigned_lead_to_cs(): void
    {
        $admin = $this->user('admin');
        $cs = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId(['contact_raw' => '+6285200000000', 'contact_key' => 'phone:6285200000000', 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($admin)->patch("/leads/{$leadId}", ['assigned_user_id' => $cs->id, 'milestones' => []]);
        $response->assertRedirect("/leads/{$leadId}");
        $response->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Lead berhasil diperbarui.',
        ]);
        $this->assertDatabaseHas('leads', ['id' => $leadId, 'assigned_user_id' => $cs->id]);
    }

    public function test_admin_can_assign_selected_leads_in_bulk_and_current_stage_follows_the_highest_milestone(): void
    {
        $admin = $this->user('admin');
        $cs = $this->user('cs');
        $firstLead = \DB::table('leads')->insertGetId(['contact_raw' => '+6285400000001', 'contact_key' => 'phone:6285400000001', 'created_at' => now(), 'updated_at' => now()]);
        $secondLead = \DB::table('leads')->insertGetId(['contact_raw' => '+6285400000002', 'contact_key' => 'phone:6285400000002', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($admin)->post('/leads/assign', [
            'lead_ids' => [$firstLead, $secondLead],
            'assigned_user_id' => $cs->id,
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', ['id' => $firstLead, 'assigned_user_id' => $cs->id]);
        $this->assertDatabaseHas('leads', ['id' => $secondLead, 'assigned_user_id' => $cs->id]);

        $this->actingAs($admin)->patch("/leads/{$firstLead}", [
            'milestones' => ['clicked', 'price_list', 'closing'],
            'next_follow_up_at' => '2026-11-12T09:30',
        ])->assertRedirect("/leads/{$firstLead}");

        $this->assertDatabaseHas('leads', [
            'id' => $firstLead,
            'current_stage' => 'closing',
            'next_follow_up_at' => '2026-11-12T09:30',
        ]);
    }

    public function test_admin_can_archive_a_lead_without_removing_its_history(): void
    {
        $admin = $this->user('admin');
        $leadId = \DB::table('leads')->insertGetId(['contact_raw' => '+6285300000000', 'contact_key' => 'phone:6285300000000', 'created_at' => now(), 'updated_at' => now()]);
        \DB::table('lead_activity')->insert(['lead_id' => $leadId, 'action' => 'Riwayat awal', 'created_at' => now()]);

        $this->actingAs($admin)->patch("/leads/{$leadId}/archive")->assertRedirect('/leads');

        $this->assertDatabaseMissing('leads', ['id' => $leadId, 'archived_at' => null]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'action' => 'Riwayat awal']);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'action' => 'Lead diarsipkan']);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
