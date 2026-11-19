<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LeadImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
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
            $table->unsignedBigInteger('default_import_user_id')->nullable();
            $table->string('google_spreadsheet_id')->nullable()->unique();
            $table->timestamp('google_sheets_synced_at')->nullable();
            $table->text('google_sheets_sync_error')->nullable();
        });
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->unsignedBigInteger('source_branch_id')->nullable();
            $table->unsignedBigInteger('source_conflict_branch_id')->nullable();
            $table->timestamp('source_conflict_at')->nullable();
            $table->string('import_fingerprint')->nullable()->unique();
            $table->string('contact_key')->nullable()->unique();
            $table->string('source_row_no')->nullable();
            $table->string('source_date_raw')->nullable();
            $table->date('lead_created_on')->nullable();
            $table->text('contact_raw')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('respond_time')->nullable();
            $table->string('imported_cs_name')->nullable();
            $table->text('imported_keterangan')->nullable();
            $table->text('raw_kebutuhan')->nullable();
            $table->text('greeting')->nullable();
            $table->text('chat_time')->nullable();
            $table->string('source_description')->nullable();
            $table->string('campaign_description')->nullable();
            $table->string('imported_definition')->nullable();
            $table->string('intent_buying')->nullable();
            $table->date('usage_date')->nullable();
            $table->string('budget')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('outcome')->nullable();
            $table->string('outcome_factor')->nullable();
            $table->text('outcome_note')->nullable();
            $table->timestamp('outcome_recorded_at')->nullable();
            $table->string('current_stage')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('crm_taken_over_at')->nullable();
            $table->unsignedBigInteger('revision')->default(0);
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
        Schema::create('lead_collaborators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamp('added_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->unique(['lead_id', 'user_id']);
        });
        Schema::create('lead_source_occurrences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->string('spreadsheet_id');
            $table->string('source_tab');
            $table->string('source_row_no')->nullable();
            $table->string('source_date_raw')->nullable();
            $table->string('source_hash')->unique();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('lead_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('status');
            $table->json('summary')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('completed_at')->nullable();
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
            'message' => 'Import selesai: 1 baru, 0 duplikat dilewati, 1 perlu ditugaskan, 0 perlu review, 1 tanpa tanggal lead.',
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

    public function test_admin_import_preserves_a_long_chat_time_from_the_source(): void
    {
        $admin = $this->user('admin');
        $chatTime = 'Diikuti pada 17 November 2026 pukul 11.13 melalui WhatsApp.';
        $csv = implode("\n", [
            'No,Nama/Nomor,Respond Time,Nama CS,Keterangan,Kebutuhan,Greeting*,Waktu Chat,Keterangan Sumber*,Keterangan Campaign*,Definisi Leads',
            "1,+62 851-7777-1222,0,Dila,tanya-tanya,nikah,halo,{$chatTime},Iklan,Bio,Warm",
        ]);

        $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => UploadedFile::fake()->createWithContent('long-chat-time.csv', $csv),
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771222',
            'chat_time' => $chatTime,
        ]);
    }

    public function test_hot_import_and_inline_update_use_the_combined_closing_repeat_order_stage(): void
    {
        $admin = $this->user('admin');
        $csv = implode("\n", [
            'No,Nama/Nomor,Respond Time,Nama CS,Keterangan,Kebutuhan,Greeting*,Waktu Chat,Keterangan Sumber*,Keterangan Campaign*,Definisi Leads',
            '1,+62 851-7777-1999,0,Dila,closing tapi belum transfer,nikah 10jt,halo,10.38,Iklan,Bio,Hot',
        ]);

        $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => UploadedFile::fake()->createWithContent('hot-leads.csv', $csv),
        ])->assertRedirect('/leads');

        $leadId = (int) \DB::table('leads')->where('contact_key', 'phone:6285177771999')->value('id');
        $this->assertDatabaseHas('leads', ['id' => $leadId, 'current_stage' => 'closing_repeat']);
        $this->assertDatabaseHas('lead_milestones', ['lead_id' => $leadId, 'milestone' => 'closing_repeat', 'is_active' => true]);

        $this->actingAs($admin)->from('/leads')->patch("/leads/{$leadId}", [
            'inline' => true,
            'milestones' => ['offline', 'online', 'closing_repeat'],
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'current_stage' => 'closing_repeat']);
    }

    public function test_cs_can_record_a_single_closing_or_loss_outcome_with_its_factor(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId]);
        $leadId = \DB::table('leads')->insertGetId([
            'name' => 'Lead outcome',
            'contact_key' => 'phone:6285112345678',
            'assigned_user_id' => $cs->id,
            'source_branch_id' => $branchId,
            'next_follow_up_at' => now()->addDay(),
            'revision' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($cs)->from('/leads')->patch("/leads/{$leadId}", [
            'inline' => true,
            'revision' => 0,
            'outcome' => 'closing',
            'outcome_factor' => 'offline_experience',
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'outcome' => 'closing',
            'outcome_factor' => 'offline_experience',
            'outcome_note' => null,
            'current_stage' => 'closing_repeat',
            'next_follow_up_at' => null,
        ]);
        $this->assertDatabaseHas('lead_activity', [
            'lead_id' => $leadId,
            'action' => 'Hasil akhir lead diperbarui',
        ]);

        $this->actingAs($cs)->from('/leads')->patch("/leads/{$leadId}", [
            'inline' => true,
            'revision' => 1,
            'outcome' => 'loss',
            'outcome_factor' => 'ghosting',
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'outcome' => 'loss',
            'outcome_factor' => 'ghosting',
        ]);
    }

    public function test_final_outcome_keeps_the_first_recorded_time_when_its_factor_is_corrected(): void
    {
        $cs = $this->user('cs');
        $recordedAt = now()->subDay()->startOfSecond();
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285112345688',
            'contact_key' => 'phone:6285112345688',
            'assigned_user_id' => $cs->id,
            'outcome' => 'loss',
            'outcome_factor' => 'ghosting',
            'outcome_recorded_at' => $recordedAt,
            'revision' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($cs)->from('/leads')->patch("/leads/{$leadId}", [
            'inline' => true,
            'revision' => 0,
            'outcome' => 'loss',
            'outcome_factor' => 'slow_response',
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'outcome_factor' => 'slow_response',
            'outcome_recorded_at' => $recordedAt,
        ]);
    }

    public function test_outcome_lainnya_requires_a_note(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Pusat']);
        $cs = $this->user('cs', ['branch_id' => $branchId]);
        $leadId = \DB::table('leads')->insertGetId([
            'contact_key' => 'phone:6285112345679',
            'assigned_user_id' => $cs->id,
            'source_branch_id' => $branchId,
            'revision' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($cs)->patch("/leads/{$leadId}", [
            'inline' => true,
            'revision' => 0,
            'outcome' => 'loss',
            'outcome_factor' => 'other',
        ])->assertSessionHasErrors('outcome_note');
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

    public function test_local_and_international_indonesian_phone_formats_are_the_same_lead(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->post('/leads', ['contact_raw' => '0812-3456-7890'])->assertRedirect();
        $this->actingAs($admin)->post('/leads', ['contact_raw' => '+62 812 3456 7890'])
            ->assertSessionHasErrors('contact_raw');

        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567890']);
    }

    public function test_international_phone_format_does_not_duplicate_a_legacy_local_phone_key(): void
    {
        $admin = $this->user('admin');
        \DB::table('leads')->insert([
            'contact_raw' => '0812-3456-7890',
            'contact_key' => 'phone:081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->post('/leads', ['contact_raw' => '+62 812 3456 7890'])
            ->assertSessionHasErrors('contact_raw');

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_admin_can_import_the_all_leads_shape_from_xlsx(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $path = tempnam(sys_get_temp_dir(), 'all-leads-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['Tanggal', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting*', 'Waktu Chat', 'Keterangan Sumber*', 'Keterangan Campaign*']));
        $writer->addRow(Row::fromValues(['31', '+62 851-7777-3333', '0', 'Dila', 'klik saja', '', 'halo', '10.38', 'Iklan', 'Bio']));
        $writer->close();

        $response = $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => new UploadedFile($path, 'all-leads.XLSX', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
            'import_mode' => 'all_leads',
            'branch_id' => $branchId,
        ]);

        $response->assertRedirect('/leads');
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177773333', 'source_date_raw' => '31', 'imported_definition' => 'Cold', 'source_branch_id' => null]);
        @unlink($path);
    }

    public function test_admin_can_import_leads_from_all_numeric_daily_tabs_in_an_xlsx(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Yogyakarta']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $cs->id]);
        $path = tempnam(sys_get_temp_dir(), 'daily-leads-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);

        $this->writeDailyLeadSheet($writer, '1', '1 November 2026', '+62 851-7777-4444');
        $writer->addNewSheetAndMakeItCurrent();
        $this->writeDailyLeadSheet($writer, '2', '2 November 2026', '+62 851-7777-5555');
        $writer->addNewSheetAndMakeItCurrent()->setName('All Leads');
        $writer->addRow(Row::fromValues(['Tanggal', 'Nama/Nomor']));
        $writer->addRow(Row::fromValues(['1', '+62 851-7777-6666']));
        $writer->close();

        $response = $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => new UploadedFile($path, 'daily-leads.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
            'import_mode' => 'daily_tabs',
            'branch_id' => $branchId,
        ]);

        $response->assertRedirect('/leads');
        $this->assertDatabaseCount('leads', 2);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177774444', 'source_date_raw' => '1 November 2026', 'lead_created_on' => '2026-11-01', 'source_branch_id' => $branchId, 'assigned_user_id' => $cs->id]);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177775555', 'source_date_raw' => '2 November 2026']);
        $this->assertDatabaseHas('lead_activity', ['action' => 'Diimpor dari tab harian', 'detail' => 'Sumber: tab 1 (1 November 2026). Ditugaskan otomatis ke CS Yogyakarta (Yogyakarta).']);
        $this->assertDatabaseMissing('leads', ['contact_key' => 'phone:6285177776666']);
        @unlink($path);
    }

    public function test_daily_xlsx_import_reads_each_whatsapp_block_and_the_definition_column(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Yogyakarta']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $cs->id]);
        $path = tempnam(sys_get_temp_dir(), 'daily-blocks-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('1');
        $writer->addRow(Row::fromValues([null, 'Monitoring Leads']));
        $writer->addRow(Row::fromValues([null, '1 November 2026']));
        $headers = ['No', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting*', 'Waktu Chat', 'Keterangan Sumber*', 'Keterangan Campaign*', 'Definisi Leads'];
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['Whatsapp 1'])));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), $headers)));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['1', '+62 851-7777-7111', '0', 'Dila', 'klik saja', '', 'Halo pertama', '10.38', 'Iklan', 'Bio', 'Cold'])));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['Whatsapp 2'])));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), $headers)));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['1', '+62 851-7777-7222', '0', 'Dila', 'diskusi online', 'nikah', 'Halo kedua', '11.40', 'Organik', 'Other', 'Hot'])));
        $writer->close();

        $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => new UploadedFile($path, 'daily-blocks.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
            'import_mode' => 'daily_tabs',
            'branch_id' => $branchId,
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177777111', 'greeting' => 'Halo pertama', 'imported_definition' => 'Cold']);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177777222', 'greeting' => 'Halo kedua', 'imported_definition' => 'Hot']);
        @unlink($path);
    }

    public function test_daily_import_normalizes_an_indonesian_source_date(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Jakarta']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Jakarta']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $cs->id]);
        $path = tempnam(sys_get_temp_dir(), 'daily-leads-id-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $this->writeDailyLeadSheet($writer, '1', '1 Januari 2027', '+62 851-7777-7777');
        $writer->close();

        $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => new UploadedFile($path, 'daily-leads-id.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
            'import_mode' => 'daily_tabs',
            'branch_id' => $branchId,
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177777777',
            'lead_created_on' => '2027-01-01',
        ]);
        @unlink($path);
    }

    public function test_cs_cannot_open_a_lead_owned_by_another_cs(): void
    {
        $owner = $this->user('cs');
        $viewer = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId(['assigned_user_id' => $owner->id, 'contact_raw' => '+6285100000000', 'contact_key' => 'phone:6285100000000', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($viewer)->get("/leads/{$leadId}")->assertNotFound();
    }

    public function test_cs_can_filter_visible_leads_by_business_date_and_work_context(): void
    {
        $cs = $this->user('cs');
        $otherCs = $this->user('cs');
        $admin = $this->user('admin');
        \DB::table('leads')->insert([
            [
                'assigned_user_id' => $cs->id,
                'contact_raw' => '+6285200000001',
                'contact_key' => 'phone:6285200000001',
                'current_stage' => 'asked',
                'intent_buying' => 'Nikahan',
                'budget' => 'Rp10,1–15 juta',
                'lead_created_on' => '2026-11-01',
                'next_follow_up_at' => now()->addDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'assigned_user_id' => $cs->id,
                'contact_raw' => '+6285200000002',
                'contact_key' => 'phone:6285200000002',
                'current_stage' => 'clicked',
                'intent_buying' => null,
                'budget' => null,
                'lead_created_on' => null,
                'next_follow_up_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'assigned_user_id' => $otherCs->id,
                'contact_raw' => '+6285200000003',
                'contact_key' => 'phone:6285200000003',
                'current_stage' => 'asked',
                'intent_buying' => 'Nikahan',
                'budget' => 'Rp10,1–15 juta',
                'lead_created_on' => '2026-11-01',
                'next_follow_up_at' => now()->addDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($cs)->get('/leads?stage=asked&intent=Nikahan&budget=Rp10%2C1%E2%80%9315%20juta&lead_date_from=2026-11-01&lead_date_to=2026-11-01&follow_up=scheduled&handling=primary')->assertInertia(
            fn (Assert $page) => $page
                ->component('Leads/Index')
                ->where('leads.total', 1)
                ->where('leads.data.0.contact_raw', '+6285200000001')
                ->where('leads.data.0.lead_created_on', '2026-11-01'),
        );

        $this->actingAs($cs)->get('/leads?lead_date_status=missing')->assertInertia(
            fn (Assert $page) => $page
                ->component('Leads/Index')
                ->where('leads.total', 1)
                ->where('leads.data.0.contact_raw', '+6285200000002'),
        );

        $sharedLeadId = (int) \DB::table('leads')->where('contact_key', 'phone:6285200000001')->value('id');
        \DB::table('lead_collaborators')->insert([
            'lead_id' => $sharedLeadId,
            'user_id' => $otherCs->id,
            'added_at' => now(),
        ]);
        \DB::table('leads')->where('id', $sharedLeadId)->update(['updated_at' => now()->addSecond()]);
        $this->actingAs($cs)->get("/leads?cs_user_id={$otherCs->id}")->assertInertia(
            fn (Assert $page) => $page
                ->component('Leads/Index')
                ->where('leads.total', 1)
                ->where('leads.data.0.contact_raw', '+6285200000001')
                ->where('leads.data.0.cs_filter_match', 'CS pendamping'),
        );

        $this->actingAs($admin)->get("/leads?cs_user_id={$otherCs->id}")->assertInertia(
            fn (Assert $page) => $page
                ->component('Leads/Index')
                ->where('leads.total', 2)
                ->where('leads.data.0.cs_filter_match', 'CS pendamping')
                ->where('leads.data.1.cs_filter_match', 'CS utama'),
        );
    }

    public function test_invalid_lead_date_filter_is_rejected_safely(): void
    {
        $cs = $this->user('cs');

        $this->actingAs($cs)
            ->get('/leads?lead_date_from=not-a-date')
            ->assertSessionHasErrors('lead_date_from');
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

    public function test_lead_list_has_the_data_needed_for_inline_quick_updates(): void
    {
        $admin = $this->user('admin');
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285100000999',
            'contact_key' => 'phone:6285100000999',
            'intent_buying' => 'Nikahan',
            'usage_date' => '2026-12-12',
            'budget' => '10 juta',
            'keterangan' => 'Menunggu referensi model.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('lead_milestones')->insert([
            'lead_id' => $leadId,
            'milestone' => 'asked',
            'is_active' => true,
            'marked_at' => now(),
        ]);
        \DB::table('lead_activity')->insert([
            'lead_id' => $leadId,
            'action' => 'Lead diperbarui',
            'detail' => 'Keterangan diperbarui',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)->get('/leads')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Leads/Index')
                ->where('leads.data.0.id', $leadId)
                ->where('leads.data.0.intent_buying', 'Nikahan')
                ->has('leads.data.0.created_at')
                ->where('leads.data.0.milestones', ['asked'])
                ->where('leads.data.0.latest_activity', 'Lead diperbarui')
                ->where('leads.data.0.latest_activity_detail', 'Keterangan diperbarui'),
            );
    }

    public function test_cs_can_view_the_five_latest_activity_entries_for_an_assigned_lead(): void
    {
        $cs = $this->user('cs');
        $crmTakenOverAt = now()->subMinute()->startOfSecond();
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $cs->id,
            'contact_raw' => '+6285100000998',
            'contact_key' => 'phone:6285100000998',
            'lead_created_on' => '2026-11-15',
            'next_follow_up_at' => now()->addHour(),
            'crm_taken_over_at' => $crmTakenOverAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach (range(1, 6) as $number) {
            \DB::table('lead_activity')->insert([
                'lead_id' => $leadId,
                'actor_id' => $cs->id,
                'action' => "Riwayat {$number}",
                'created_at' => now()->addSeconds($number),
            ]);
        }

        $this->actingAs($cs)
            ->getJson("/leads/{$leadId}/history-preview")
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('data.0.action', 'Riwayat 6')
            ->assertJsonPath('data.4.action', 'Riwayat 2')
            ->assertJsonPath('meta.lead_created_on', '2026-11-15')
            ->assertJsonPath('meta.crm_taken_over_at', $crmTakenOverAt->toDateTimeString())
            ->assertJsonStructure(['meta' => ['last_activity_at', 'next_follow_up_at', 'crm_taken_over_at']]);
    }

    public function test_cs_cannot_view_activity_entries_for_an_unassigned_lead(): void
    {
        $owner = $this->user('cs');
        $viewer = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $owner->id,
            'contact_raw' => '+6285100000997',
            'contact_key' => 'phone:6285100000997',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($viewer)
            ->getJson("/leads/{$leadId}/history-preview")
            ->assertNotFound();
    }

    public function test_inline_lead_update_returns_to_the_lead_list(): void
    {
        $admin = $this->user('admin');
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285100000998',
            'contact_key' => 'phone:6285100000998',
            'next_follow_up_at' => '2026-12-20 10:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->from('/leads')->patch("/leads/{$leadId}", [
            'inline' => true,
            'intent_buying' => 'Nikahan',
            'usage_date' => '2026-12-12',
            'budget' => 'Rp5,1–10 juta',
            'keterangan' => 'Update dari daftar lead.',
            'milestones' => ['asked'],
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'current_stage' => 'asked',
            'keterangan' => 'Update dari daftar lead.',
            'next_follow_up_at' => '2026-12-20 10:00:00',
        ]);
    }

    public function test_budget_updates_only_accept_the_supported_ranges(): void
    {
        $admin = $this->user('admin');
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285100000994',
            'contact_key' => 'phone:6285100000994',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->patch("/leads/{$leadId}", [
            'inline' => true,
            'budget' => '10 juta',
        ])->assertSessionHasErrors('budget');

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'budget' => null]);
    }

    public function test_importing_the_same_contact_from_a_new_daily_tab_records_a_reappearance(): void
    {
        $rows = [[
            'source_row_no' => '1',
            'source_date_raw' => '1 November 2026',
            'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1999 Rini',
            'respond_time' => '0',
            'imported_cs_name' => 'Dila',
            'imported_keterangan' => 'tanya-tanya',
            'raw_kebutuhan' => 'cincin nikah',
            'greeting' => 'Halo',
            'chat_time' => '10.38',
            'source_description' => 'Iklan',
            'campaign_description' => 'Bio',
            'imported_definition' => 'Warm',
        ]];

        $first = app(LeadImportService::class)->import($rows, null, 'sheet-utama');
        $again = app(LeadImportService::class)->import($rows, null, 'sheet-utama');
        $rows[0]['source_tab'] = '2';
        $rows[0]['source_date_raw'] = '2 November 2026';
        $reappeared = app(LeadImportService::class)->import($rows, null, 'sheet-utama');

        $this->assertSame(['enriched' => 0, 'inserted' => 1, 'skipped' => 0, 'resurfaced' => 0, 'unassigned' => 1, 'needs_review' => 0, 'missing_dates' => 0], $first);
        $this->assertSame(['enriched' => 0, 'inserted' => 0, 'skipped' => 1, 'resurfaced' => 0, 'unassigned' => 0, 'needs_review' => 0, 'missing_dates' => 0], $again);
        $this->assertSame(['enriched' => 0, 'inserted' => 0, 'skipped' => 0, 'resurfaced' => 1, 'unassigned' => 0, 'needs_review' => 0, 'missing_dates' => 0], $reappeared);
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('lead_source_occurrences', 2);
        $this->assertDatabaseHas('lead_activity', ['action' => 'Lead muncul kembali dari Google Sheets']);
    }

    public function test_reimporting_the_same_source_row_enriches_source_data_without_touching_cs_work(): void
    {
        $row = [
            'source_row_no' => '5',
            'source_date_raw' => '1 November 2026',
            'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1777 Rini',
            'respond_time' => null,
            'imported_cs_name' => 'Dila',
            'imported_keterangan' => 'klik saja',
            'raw_kebutuhan' => null,
            'greeting' => null,
            'chat_time' => '10.38',
            'source_description' => 'Iklan',
            'campaign_description' => 'Bio',
            'imported_definition' => 'Cold',
        ];
        app(LeadImportService::class)->import([$row], null, 'sheet-utama');
        $leadId = (int) \DB::table('leads')->where('contact_key', 'phone:6285177771777')->value('id');
        \DB::table('leads')->where('id', $leadId)->update(['keterangan' => 'Catatan follow-up CS', 'current_stage' => 'asked']);

        $row['greeting'] = 'Halo, saya ingin konsultasi cincin.';
        $row['raw_kebutuhan'] = 'Cincin nikah';
        $summary = app(LeadImportService::class)->import([$row], null, 'sheet-utama');

        $this->assertSame(1, $summary['enriched']);
        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'greeting' => 'Halo, saya ingin konsultasi cincin.',
            'raw_kebutuhan' => 'Cincin nikah',
            'keterangan' => 'Catatan follow-up CS',
            'current_stage' => 'asked',
        ]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'action' => 'Data sumber diperbarui dari Google Sheets']);
    }

    public function test_moving_a_row_inside_the_same_daily_tab_keeps_the_same_lead(): void
    {
        $row = [
            'source_row_no' => 'Whatsapp 1 #1',
            'source_date_raw' => '1 November 2026',
            'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1778 Rini',
            'respond_time' => '0',
            'imported_cs_name' => 'Dila',
            'imported_keterangan' => 'Tanya-Tanya',
            'raw_kebutuhan' => 'Cincin nikah',
            'greeting' => 'Halo',
            'chat_time' => '10.38',
            'source_description' => 'Iklan',
            'campaign_description' => 'Bio',
            'imported_definition' => 'Warm',
        ];
        app(LeadImportService::class)->import([$row], null, 'sheet-utama');

        $row['source_row_no'] = 'Whatsapp 2 #1';
        $row['greeting'] = 'Halo, saya ingin konsultasi cincin.';
        $summary = app(LeadImportService::class)->import([$row], null, 'sheet-utama');

        $this->assertSame(1, $summary['enriched']);
        $this->assertSame(0, $summary['resurfaced']);
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('lead_source_occurrences', 2);
        $this->assertDatabaseMissing('lead_activity', ['action' => 'Lead muncul kembali dari Google Sheets']);
    }

    public function test_import_from_a_branch_assigns_its_default_import_cs_instead_of_the_source_name(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Yogyakarta 1']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Yogyakarta 2']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $cs->id]);
        $rows = [[
            'source_row_no' => '1',
            'source_date_raw' => '1 November 2026',
            'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1888 Rini',
            'respond_time' => '0',
            'imported_cs_name' => 'Nama lama di spreadsheet',
            'imported_keterangan' => 'tanya-tanya',
            'raw_kebutuhan' => 'cincin nikah',
            'greeting' => 'Halo',
            'chat_time' => '10.38',
            'source_description' => 'Iklan',
            'campaign_description' => 'Bio',
            'imported_definition' => 'Warm',
        ]];

        app(LeadImportService::class)->import($rows, null, 'sheet-yogyakarta', $branchId);

        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771888',
            'source_branch_id' => $branchId,
            'assigned_user_id' => $cs->id,
        ]);
    }

    public function test_same_contact_from_a_different_branch_is_flagged_without_changing_its_owner(): void
    {
        $yogyakarta = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $solo = \DB::table('branches')->insertGetId(['name' => 'Solo']);
        $yogyakartaCs = $this->user('cs', ['branch_id' => $yogyakarta, 'name' => 'CS Yogyakarta']);
        $soloCs = $this->user('cs', ['branch_id' => $solo, 'name' => 'CS Solo']);
        \DB::table('branches')->where('id', $yogyakarta)->update(['default_import_user_id' => $yogyakartaCs->id]);
        \DB::table('branches')->where('id', $solo)->update(['default_import_user_id' => $soloCs->id]);
        $row = [
            'source_row_no' => '1',
            'source_date_raw' => '1 November 2026',
            'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1998 Rini',
            'respond_time' => '0',
            'imported_cs_name' => 'CS lama',
            'imported_keterangan' => 'tanya-tanya',
            'raw_kebutuhan' => 'cincin nikah',
            'greeting' => 'Halo',
            'chat_time' => '10.38',
            'source_description' => 'Iklan',
            'campaign_description' => 'Bio',
            'imported_definition' => 'Warm',
        ];

        app(LeadImportService::class)->import([$row], null, 'sheet-yogyakarta', $yogyakarta);
        $row['source_tab'] = '2';
        $row['source_date_raw'] = '2 November 2026';
        $summary = app(LeadImportService::class)->import([$row], null, 'sheet-solo', $solo);

        $this->assertSame(1, $summary['resurfaced']);
        $this->assertSame(1, $summary['needs_review']);
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771998',
            'assigned_user_id' => $yogyakartaCs->id,
            'source_branch_id' => $yogyakarta,
            'source_conflict_branch_id' => $solo,
        ]);
        $this->assertDatabaseHas('lead_activity', ['action' => 'Konflik sumber cabang']);
    }

    public function test_admin_can_filter_unassigned_leads_by_their_source_branch(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285177771887',
            'contact_key' => 'phone:6285177771887',
            'source_branch_id' => $branchId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->get("/leads?branch={$branchId}")
            ->assertInertia(fn (Assert $page) => $page
                ->where('leads.data.0.id', $leadId)
                ->where('leads.data.0.branch_name', 'Yogyakarta'),
            );
    }

    public function test_admin_branch_filter_uses_source_branch_for_assigned_leads(): void
    {
        $admin = $this->user('admin');
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $bandungId = \DB::table('branches')->insertGetId(['name' => 'Bandung']);
        $bandungCs = $this->user('cs', ['branch_id' => $bandungId]);
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $bandungCs->id,
            'source_branch_id' => $palaganId,
            'contact_raw' => '+6285177771888',
            'contact_key' => 'phone:6285177771888',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->get("/leads?branch={$palaganId}")
            ->assertInertia(fn (Assert $page) => $page
                ->where('leads.data.0.id', $leadId),
            );
        $this->actingAs($admin)->get("/leads?branch={$bandungId}")
            ->assertInertia(fn (Assert $page) => $page
                ->where('leads.data', []),
            );
    }

    public function test_admin_can_filter_leads_without_a_source_branch(): void
    {
        $admin = $this->user('admin');
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285177771889',
            'contact_key' => 'phone:6285177771889',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->get('/leads?source_branch_missing=1')
            ->assertInertia(fn (Assert $page) => $page
                ->where('leads.data.0.id', $leadId)
                ->where('filters.source_branch_missing', '1'),
            );
    }

    public function test_inline_lead_creation_returns_to_the_lead_list(): void
    {
        $cs = $this->user('cs');

        $this->actingAs($cs)->from('/leads')->post('/leads', [
            'inline' => true,
            'contact_raw' => '+6285100000997',
            'milestone' => 'asked',
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285100000997',
            'assigned_user_id' => $cs->id,
        ]);
    }

    public function test_inline_lead_update_can_schedule_the_next_follow_up(): void
    {
        $admin = $this->user('admin');
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285100000995',
            'contact_key' => 'phone:6285100000995',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->from('/leads')->patch("/leads/{$leadId}", [
            'inline' => true,
            'next_follow_up_at' => '2026-12-25T10:00',
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'next_follow_up_at' => '2026-12-25 10:00:00',
        ]);
    }

    public function test_cs_cannot_update_another_cs_lead_from_inline_workspace(): void
    {
        $owner = $this->user('cs');
        $viewer = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $owner->id,
            'contact_raw' => '+6285100000996',
            'contact_key' => 'phone:6285100000996',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($viewer)->from('/leads')->patch("/leads/{$leadId}", [
            'inline' => true,
            'milestones' => ['asked'],
        ])->assertNotFound();
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
            'milestones' => ['clicked', 'price_list', 'closing_repeat'],
            'next_follow_up_at' => '2026-11-12T09:30',
        ])->assertRedirect("/leads/{$firstLead}");

        $this->assertDatabaseHas('leads', [
            'id' => $firstLead,
            'current_stage' => 'closing_repeat',
            'next_follow_up_at' => '2026-11-12 09:30:00',
        ]);
    }

    public function test_incomplete_qualification_queue_matches_dashboard_priority(): void
    {
        $admin = $this->user('admin');
        $incompleteLead = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285500000001',
            'contact_key' => 'phone:6285500000001',
            'intent_buying' => 'Nikahan',
            'budget' => '10 juta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('leads')->insert([
            'contact_raw' => '+6285500000002',
            'contact_key' => 'phone:6285500000002',
            'intent_buying' => 'Nikahan',
            'usage_date' => '2026-12-12',
            'budget' => '10 juta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->get('/leads?queue=needs_qualification')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Leads/Index')
                ->where('leads.total', 1)
                ->where('leads.data.0.id', $incompleteLead),
            );
    }

    public function test_cs_can_open_its_dashboard_priority_queues(): void
    {
        $cs = $this->user('cs');
        $priorityLead = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $cs->id,
            'contact_raw' => '+6285500000003',
            'contact_key' => 'phone:6285500000003',
            'intent_buying' => 'Nikahan',
            'budget' => '10 juta',
            'next_follow_up_at' => now()->subMinute(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('leads')->insert([
            'assigned_user_id' => $cs->id,
            'contact_raw' => '+6285500000004',
            'contact_key' => 'phone:6285500000004',
            'intent_buying' => 'Nikahan',
            'usage_date' => '2026-12-12',
            'budget' => '10 juta',
            'next_follow_up_at' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($cs)->get('/leads?queue=needs_qualification')
            ->assertInertia(fn (Assert $page) => $page
                ->where('leads.total', 1)
                ->where('leads.data.0.id', $priorityLead),
            );
        $this->actingAs($cs)->get('/leads?queue=follow_up_due')
            ->assertInertia(fn (Assert $page) => $page
                ->where('leads.total', 1)
                ->where('leads.data.0.id', $priorityLead),
            );
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

    public function test_lead_history_can_load_older_entries(): void
    {
        $admin = $this->user('admin');
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285300000001',
            'contact_key' => 'phone:6285300000001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach (range(1, 31) as $number) {
            \DB::table('lead_activity')->insert([
                'lead_id' => $leadId,
                'action' => "Riwayat {$number}",
                'created_at' => now(),
            ]);
        }

        $this->actingAs($admin)
            ->get("/leads/{$leadId}?history_page=2")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Leads/Show')
                ->where('history.current_page', 2)
                ->where('history.total', 31),
            );
    }

    public function test_cs_can_create_a_lead_that_is_automatically_assigned_to_them(): void
    {
        $cs = $this->user('cs');
        $otherCs = $this->user('cs');

        $response = $this->actingAs($cs)->post('/leads', [
            'contact_raw' => '+62 851-7777-7777',
            'assigned_user_id' => $otherCs->id,
            'milestone' => 'asked',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177777777',
            'assigned_user_id' => $cs->id,
            'current_stage' => 'asked',
        ]);
    }

    public function test_cs_can_bulk_archive_and_restore_only_their_own_leads(): void
    {
        $cs = $this->user('cs');
        $otherCs = $this->user('cs');
        $ownedLead = \DB::table('leads')->insertGetId(['assigned_user_id' => $cs->id, 'contact_raw' => '+6285100000001', 'contact_key' => 'phone:6285100000001', 'created_at' => now(), 'updated_at' => now()]);
        $otherLead = \DB::table('leads')->insertGetId(['assigned_user_id' => $otherCs->id, 'contact_raw' => '+6285100000002', 'contact_key' => 'phone:6285100000002', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($cs)->post('/leads/archive', ['lead_ids' => [$ownedLead]])->assertRedirect('/leads');
        $this->assertDatabaseMissing('leads', ['id' => $ownedLead, 'archived_at' => null]);

        $this->actingAs($cs)->post('/leads/archive', ['lead_ids' => [$otherLead]])->assertNotFound();

        $this->actingAs($cs)->post('/leads/restore', ['lead_ids' => [$ownedLead]])->assertRedirect('/leads?archived=1');
        $this->assertDatabaseHas('leads', ['id' => $ownedLead, 'archived_at' => null]);
    }

    public function test_cs_can_permanently_delete_only_their_own_archived_leads(): void
    {
        $cs = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId(['assigned_user_id' => $cs->id, 'contact_raw' => '+6285100000003', 'contact_key' => 'phone:6285100000003', 'archived_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        \DB::table('lead_milestones')->insert(['lead_id' => $leadId, 'milestone' => 'asked', 'is_active' => true, 'marked_at' => now()]);
        \DB::table('lead_activity')->insert(['lead_id' => $leadId, 'action' => 'Lead diarsipkan', 'created_at' => now()]);

        $this->actingAs($cs)->delete('/leads', ['lead_ids' => [$leadId]])->assertRedirect('/leads?archived=1');

        $this->assertDatabaseMissing('leads', ['id' => $leadId]);
        $this->assertDatabaseMissing('lead_milestones', ['lead_id' => $leadId]);
        $this->assertDatabaseMissing('lead_activity', ['lead_id' => $leadId]);
    }

    public function test_admin_can_add_a_cs_pendamping_who_can_update_the_lead(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $owner = $this->user('cs', ['branch_id' => $branchId]);
        $collaborator = $this->user('cs', ['branch_id' => $branchId]);
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $owner->id,
            'source_branch_id' => $branchId,
            'contact_raw' => '+6285100000011',
            'contact_key' => 'phone:6285100000011',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch("/leads/{$leadId}/collaborators", ['user_ids' => [$collaborator->id]])
            ->assertRedirect();
        $this->assertDatabaseHas('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $collaborator->id, 'removed_at' => null]);

        $this->actingAs($collaborator)
            ->patch("/leads/{$leadId}", ['inline' => true, 'keterangan' => 'Follow-up oleh CS pendamping'])
            ->assertRedirect();
        $this->assertDatabaseHas('leads', ['id' => $leadId, 'keterangan' => 'Follow-up oleh CS pendamping']);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'actor_id' => $collaborator->id]);

        $this->actingAs($admin)
            ->patch("/leads/{$leadId}/collaborators", ['user_ids' => []])
            ->assertRedirect();
        $this->assertDatabaseMissing('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $collaborator->id, 'removed_at' => null]);
        $this->actingAs($collaborator)->get("/leads/{$leadId}")->assertInertia(
            fn (Assert $page) => $page
                ->component('Leads/Show')
                ->where('handlingRole', 'team'),
        );
    }

    public function test_only_admin_can_mark_a_lead_as_taken_over_by_crm(): void
    {
        $admin = $this->user('admin');
        $cs = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $cs->id,
            'contact_raw' => '+6285100000012',
            'contact_key' => 'phone:6285100000012',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($cs)
            ->patch("/leads/{$leadId}", ['inline' => true, 'crm_taken_over' => true])
            ->assertForbidden();
        $this->assertDatabaseHas('leads', ['id' => $leadId, 'crm_taken_over_at' => null]);

        $this->actingAs($admin)
            ->patch("/leads/{$leadId}", ['crm_taken_over' => true])
            ->assertRedirect("/leads/{$leadId}");
        $this->assertDatabaseMissing('leads', ['id' => $leadId, 'crm_taken_over_at' => null]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'action' => 'Lead diperbarui']);
    }

    public function test_overdue_lead_without_newer_activity_is_marked_for_crm(): void
    {
        $overdueLead = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285100000013',
            'contact_key' => 'phone:6285100000013',
            'next_follow_up_at' => now()->subMinute(),
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);
        $activeLead = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285100000014',
            'contact_key' => 'phone:6285100000014',
            'next_follow_up_at' => now()->subMinute(),
            'created_at' => now()->subHours(2),
            'updated_at' => now(),
        ]);
        $unscheduledLead = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285100000017',
            'contact_key' => 'phone:6285100000017',
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);
        $closedLead = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285100000018',
            'contact_key' => 'phone:6285100000018',
            'outcome' => 'loss',
            'outcome_factor' => 'ghosting',
            'next_follow_up_at' => now()->subMinute(),
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);
        \DB::table('lead_activity')->insert([
            'lead_id' => $activeLead,
            'action' => 'Lead diperbarui',
            'created_at' => now(),
        ]);

        Artisan::call('leads:mark-overdue-crm');

        $this->assertDatabaseMissing('leads', ['id' => $overdueLead, 'crm_taken_over_at' => null]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $overdueLead, 'action' => 'Lead ditandai CRM otomatis']);
        $this->assertDatabaseHas('leads', ['id' => $activeLead, 'crm_taken_over_at' => null]);
        $this->assertDatabaseHas('leads', ['id' => $unscheduledLead, 'crm_taken_over_at' => null]);
        $this->assertDatabaseHas('leads', ['id' => $closedLead, 'crm_taken_over_at' => null]);
    }

    public function test_admin_can_add_a_cs_pendamping_to_selected_leads(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $owner = $this->user('cs', ['branch_id' => $branchId]);
        $collaborator = $this->user('cs', ['branch_id' => $branchId]);
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $owner->id,
            'source_branch_id' => $branchId,
            'contact_raw' => '+6285100000015',
            'contact_key' => 'phone:6285100000015',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post('/leads/collaborators', ['lead_ids' => [$leadId], 'user_id' => $collaborator->id])
            ->assertRedirect('/leads');

        $this->assertDatabaseHas('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $collaborator->id, 'removed_at' => null]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'action' => 'CS pendamping ditambahkan']);
    }

    public function test_cs_can_see_leads_from_its_branch_but_not_other_branches(): void
    {
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $bandungId = \DB::table('branches')->insertGetId(['name' => 'Bandung']);
        $primary = $this->user('cs', ['branch_id' => $palaganId]);
        $backup = $this->user('cs', ['branch_id' => $palaganId]);
        $otherBranchCs = $this->user('cs', ['branch_id' => $bandungId]);

        \DB::table('leads')->insert([
            [
                'name' => 'Lead Palagan',
                'assigned_user_id' => $primary->id,
                'source_branch_id' => $palaganId,
                'contact_raw' => '+6285100000021',
                'contact_key' => 'phone:6285100000021',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lead Bandung',
                'assigned_user_id' => $otherBranchCs->id,
                'source_branch_id' => $bandungId,
                'contact_raw' => '+6285100000022',
                'contact_key' => 'phone:6285100000022',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($backup)->get('/leads')->assertInertia(
            fn (Assert $page) => $page
                ->component('Leads/Index')
                ->has('leads.data', 1)
                ->where('leads.data.0.name', 'Lead Palagan')
                ->where('leads.data.0.handling_role', 'team'),
        );
    }

    public function test_cs_can_start_and_finish_assisting_a_lead_from_its_branch(): void
    {
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $primary = $this->user('cs', ['name' => 'CS Utama', 'branch_id' => $palaganId]);
        $backup = $this->user('cs', ['name' => 'CS Pendamping', 'branch_id' => $palaganId]);
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $primary->id,
            'source_branch_id' => $palaganId,
            'contact_raw' => '+6285100000023',
            'contact_key' => 'phone:6285100000023',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($backup)
            ->post("/leads/{$leadId}/start-assisting")
            ->assertRedirect('/leads');

        $this->assertDatabaseHas('lead_collaborators', [
            'lead_id' => $leadId,
            'user_id' => $backup->id,
            'removed_at' => null,
        ]);
        $this->assertDatabaseHas('lead_activity', [
            'lead_id' => $leadId,
            'actor_id' => $backup->id,
            'action' => 'CS mulai membantu',
        ]);

        $this->actingAs($backup)
            ->delete("/leads/{$leadId}/stop-assisting")
            ->assertRedirect('/leads');

        $this->assertDatabaseMissing('lead_collaborators', [
            'lead_id' => $leadId,
            'user_id' => $backup->id,
            'removed_at' => null,
        ]);
        $this->assertDatabaseHas('lead_activity', [
            'lead_id' => $leadId,
            'actor_id' => $backup->id,
            'action' => 'CS selesai membantu',
        ]);
    }

    public function test_cs_only_receives_cs_filter_options_from_its_own_branch(): void
    {
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $bandungId = \DB::table('branches')->insertGetId(['name' => 'Bandung']);
        $palaganCs = $this->user('cs', ['name' => 'CS Palagan', 'branch_id' => $palaganId]);
        $this->user('cs', ['name' => 'CS Bandung', 'branch_id' => $bandungId]);

        $this->actingAs($palaganCs)->get('/leads')->assertInertia(
            fn (Assert $page) => $page
                ->component('Leads/Index')
                ->has('csAccounts', 1)
                ->where('csAccounts.0.name', 'CS Palagan')
                ->where('csAccounts.0.branch_id', $palaganId),
        );
    }

    public function test_cs_cannot_start_assisting_a_lead_from_another_branch(): void
    {
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $bandungId = \DB::table('branches')->insertGetId(['name' => 'Bandung']);
        $palaganCs = $this->user('cs', ['branch_id' => $palaganId]);
        $bandungCs = $this->user('cs', ['branch_id' => $bandungId]);
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $bandungCs->id,
            'source_branch_id' => $bandungId,
            'contact_raw' => '+6285100000024',
            'contact_key' => 'phone:6285100000024',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($palaganCs)
            ->post("/leads/{$leadId}/start-assisting")
            ->assertNotFound();

        $this->assertDatabaseMissing('lead_collaborators', [
            'lead_id' => $leadId,
            'user_id' => $palaganCs->id,
        ]);
    }

    public function test_team_member_cannot_update_or_archive_a_lead_before_starting_to_assist(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $primary = $this->user('cs', ['branch_id' => $branchId]);
        $teamMember = $this->user('cs', ['branch_id' => $branchId]);
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $primary->id,
            'source_branch_id' => $branchId,
            'contact_raw' => '+6285100000025',
            'contact_key' => 'phone:6285100000025',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($teamMember)
            ->patch("/leads/{$leadId}", ['inline' => true, 'keterangan' => 'Tidak boleh tersimpan'])
            ->assertNotFound();
        $this->actingAs($teamMember)
            ->patch("/leads/{$leadId}/archive")
            ->assertNotFound();

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'keterangan' => null, 'archived_at' => null]);
    }

    public function test_admin_cannot_assign_or_add_a_pendamping_from_a_different_source_branch(): void
    {
        $admin = $this->user('admin');
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $bandungId = \DB::table('branches')->insertGetId(['name' => 'Bandung']);
        $palaganCs = $this->user('cs', ['branch_id' => $palaganId]);
        $bandungCs = $this->user('cs', ['branch_id' => $bandungId]);
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $palaganCs->id,
            'source_branch_id' => $palaganId,
            'contact_raw' => '+6285100000026',
            'contact_key' => 'phone:6285100000026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post('/leads/assign', ['lead_ids' => [$leadId], 'assigned_user_id' => $bandungCs->id])
            ->assertSessionHasErrors('assigned_user_id');
        $this->actingAs($admin)
            ->post('/leads/collaborators', ['lead_ids' => [$leadId], 'user_id' => $bandungCs->id])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'assigned_user_id' => $palaganCs->id]);
        $this->assertDatabaseMissing('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $bandungCs->id]);
    }

    public function test_first_admin_assignment_sets_the_lead_source_branch_and_keeps_the_current_list_filter(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId]);
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285100000027',
            'contact_key' => 'phone:6285100000027',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post('/leads/assign', ['lead_ids' => [$leadId], 'assigned_user_id' => $cs->id])
            ->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'assigned_user_id' => $cs->id,
            'source_branch_id' => $branchId,
        ]);

        $backup = $this->user('cs', ['branch_id' => $branchId]);
        $this->actingAs($backup)
            ->from('/leads?handling=team')
            ->post("/leads/{$leadId}/start-assisting", ['return_to' => '/leads?handling=team'])
            ->assertRedirect('/leads?handling=team');
    }

    public function test_manual_lead_created_by_cs_uses_their_branch_as_its_source(): void
    {
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $palaganId]);

        $this->actingAs($cs)->post('/leads', [
            'contact_raw' => '+6285100000024',
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285100000024',
            'source_branch_id' => $palaganId,
        ]);
    }

    public function test_stale_inline_lead_update_is_rejected_instead_of_overwriting(): void
    {
        $cs = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId([
            'assigned_user_id' => $cs->id,
            'contact_raw' => '+6285100000016',
            'contact_key' => 'phone:6285100000016',
            'keterangan' => 'Catatan baru',
            'revision' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($cs)
            ->patch("/leads/{$leadId}", ['inline' => true, 'revision' => 0, 'keterangan' => 'Catatan lama'])
            ->assertSessionHasErrors('revision');

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'keterangan' => 'Catatan baru', 'revision' => 1]);
    }

    /** @param array<string, mixed> $attributes */
    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->forceFill(['role' => $role] + $attributes)->save();

        return $user;
    }

    private function writeDailyLeadSheet(Writer $writer, string $tab, string $date, string $phone): void
    {
        $writer->getCurrentSheet()->setName($tab);
        $writer->addRow(Row::fromValues([null, 'Monitoring Leads']));
        $writer->addRow(Row::fromValues([null, $date]));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['Whatsapp 1'])));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['No', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting*', 'Waktu Chat', 'Keterangan Sumber*', 'Keterangan Campaign*', 'Definisi Leads'])));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['1', $phone, '0', 'Dila', 'klik saja', 'nikah', 'halo', '10.38', 'Iklan', 'Bio', 'Cold'])));
    }
}
