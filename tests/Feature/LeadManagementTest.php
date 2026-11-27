<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GoogleSheetSyncService;
use App\Services\LeadImportService;
use App\Support\DailyLeadTabParser;
use App\Support\LeadSourceDate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\FormulaCell;
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
        Schema::create('cs_source_name_aliases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');
            $table->string('source_name');
            $table->string('source_name_key');
            $table->timestamp('created_at')->nullable();
            $table->unique(['branch_id', 'source_name_key']);
        });
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->string('owner_resolution')->default('manual');
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
            $table->unsignedBigInteger('source_branch_id')->nullable();
            $table->string('spreadsheet_id');
            $table->string('source_tab');
            $table->string('source_row_no')->nullable();
            $table->string('source_date_raw')->nullable();
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
    }

    public function test_google_sheet_sync_refuses_to_overlap_an_existing_sync(): void
    {
        $lock = Cache::lock('lead-google-sheets-sync', 120);
        $this->assertTrue($lock->get());

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Sinkronisasi Google Sheets sedang berjalan');

            app(GoogleSheetSyncService::class)->sync();
        } finally {
            $lock->release();
        }
    }

    public function test_file_import_refuses_to_run_while_sheet_sync_holds_the_lock(): void
    {
        $admin = $this->user('admin');
        $csv = "No,Nama/Nomor,Respond Time,Nama CS,Keterangan,Kebutuhan,Greeting*,Waktu Chat,Keterangan Sumber*,Keterangan Campaign*,Definisi Leads\n1,081234567890,0,Dilla,klik saja,nikah,halo,10.00,Iklan,Bio,Cold";
        $lock = Cache::lock('lead-google-sheets-sync', 120);
        $this->assertTrue($lock->get());
        try {
            $this->actingAs($admin)->from('/branches')->post('/leads/import', ['csv_file' => UploadedFile::fake()->createWithContent('leads.csv', $csv), 'return_to' => 'branches'])->assertSessionHasErrors('csv_file');
            $this->assertDatabaseCount('leads', 0);
        } finally {
            $lock->release();
        }
        $this->actingAs($admin)->from('/branches')->post('/leads/import', ['csv_file' => UploadedFile::fake()->createWithContent('leads.csv', $csv), 'return_to' => 'branches'])->assertRedirect('/branches')->assertSessionHasNoErrors();
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_sheet_sync_is_blocked_during_file_import(): void
    {
        $admin = $this->user('admin');
        $csv = "No,Nama/Nomor,Respond Time,Nama CS,Keterangan,Kebutuhan,Greeting*,Waktu Chat,Keterangan Sumber*,Keterangan Campaign*,Definisi Leads\n1,081234567890,0,Dilla,klik saja,nikah,halo,10.00,Iklan,Bio,Cold";
        $importer = Mockery::mock(LeadImportService::class);
        $importer->shouldReceive('import')->once()->andReturnUsing(function () {
            try {
                app(GoogleSheetSyncService::class)->sync();
                $this->fail('Sinkronisasi tidak boleh berjalan bersamaan dengan impor.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('sedang berjalan', $exception->getMessage());
            }

            return ['inserted' => 0, 'skipped' => 0, 'unassigned' => 0, 'needs_review' => 0, 'missing_dates' => 0, 'awaiting_account' => 0];
        });
        $this->app->instance(LeadImportService::class, $importer);
        $this->actingAs($admin)->post('/leads/import', ['csv_file' => UploadedFile::fake()->createWithContent('leads.csv', $csv)])->assertSessionHasNoErrors();
    }

    public function test_google_sheet_sync_rejects_an_incomplete_batch_response(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        Http::fake(fn (Request $request) => str_contains($request->url(), 'fields=sheets.properties')
            ? Http::response(['properties' => ['title' => 'November 2026'], 'sheets' => [['properties' => ['title' => '1']], ['properties' => ['title' => '2']]]])
            : Http::response(['valueRanges' => [['values' => []]]]));
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tidak mencakup seluruh tab');
        $sync->sync(null, $branchId);
    }

    public function test_reconciliation_rejects_malformed_sheet_response(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        Http::fake(fn (Request $request) => str_contains($request->url(), 'fields=sheets.properties')
            ? Http::response(['properties' => ['title' => 'November 2026'], 'sheets' => [['properties' => ['title' => '1']]]])
            : Http::response(['valueRanges' => [null]]));
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Respons Google Sheets');
        $sync->previewReconciliation($branchId);
    }

    public function test_reconciliation_reports_google_sheets_access_failure(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        Http::fake(['*' => Http::response([], 403)]);
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Google Sheets tidak dapat diakses');
        $sync->previewReconciliation($branchId);
    }

    public function test_reconciliation_keeps_manual_leads_and_detects_legacy_imports_and_archived_leads(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $importer = app(LeadImportService::class);
        $row = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        $importer->import([$row, [...$row, 'source_row_no' => '2', 'contact_raw' => '081234567891']], null, 'sheet', $branchId);
        $legacy = (int) \DB::table('leads')->where('contact_raw', '081234567891')->value('id');
        \DB::table('leads')->where('id', $legacy)->update(['import_fingerprint' => null]);
        $archived = (int) \DB::table('leads')->where('contact_raw', '081234567890')->value('id');
        \DB::table('leads')->where('id', $archived)->update(['archived_at' => now()]);
        $manual = \DB::table('leads')->insertGetId(['source_branch_id' => $branchId, 'contact_key' => 'phone:6281234567892', 'contact_raw' => '081234567892']);
        \DB::table('lead_activity')->insert(['lead_id' => $manual, 'action' => 'Lead dibuat manual', 'created_at' => now()]);
        $preview = $importer->previewReconciliation([$row], $branchId);
        $this->assertSame([$legacy], array_column($preview['archive_candidates'], 'lead_id'));
        $this->assertSame([$archived], array_column($preview['reappeared_archived'], 'lead_id'));
        $this->assertSame(1, $importer->archiveMissing($preview['archive_candidates'], $branchId, $this->user('admin')->id));
        $this->assertNotNull(\DB::table('leads')->where('id', $legacy)->value('archived_at'));
        $this->assertDatabaseHas('leads', ['id' => $manual, 'archived_at' => null]);
        $this->assertNotNull(\DB::table('leads')->where('id', $archived)->value('archived_at'));
    }

    public function test_reconciliation_blocks_archiving_when_data_is_outside_import_limits(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        $admin = $this->user('admin');
        $row = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        app(LeadImportService::class)->import([$row], null, 'sheet', $branchId);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $headers = ['No', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting', 'Waktu Chat', 'Keterangan Sumber', 'Keterangan Campaign', 'Definisi Leads'];
        Http::fake(fn (Request $request) => str_contains($request->url(), 'fields=sheets.properties')
            ? Http::response(['properties' => ['title' => 'November 2026'], 'sheets' => [['properties' => ['title' => '1']]]])
            : Http::response(['valueRanges' => [['range' => "'1'!A1:BA6", 'values' => [[], [], ['Whatsapp 1'], $headers, ['1', '081234567891'], [...array_fill(0, 52, ''), 'data']]]]]));
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->twice()->andReturn('token');
        $plan = $sync->previewReconciliation($branchId);
        $this->assertNotEmpty($plan['coverage_errors']);
        try {
            $sync->archiveMissing($branchId, $admin->id, hash('sha256', json_encode($plan, JSON_THROW_ON_ERROR)));
            $this->fail('Sumber yang terpotong tidak boleh mengarsipkan lead.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Buat pratinjau baru', $exception->getMessage());
        }
        $this->assertSame(0, \DB::table('leads')->whereNotNull('archived_at')->count());
    }

    public function test_google_sheet_sync_records_a_partial_branch_when_valid_daily_tabs_are_imported(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet-palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Palagan']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $cs->id]);
        config(['leads.google_sheets.credentials_path' => __FILE__]);

        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');
        $sync->shouldReceive('readBranch')->once()->andReturn([
            'rows' => [[
                'source_row_no' => 'Whatsapp 1 #1',
                'source_date_raw' => '1 November 2026',
                'source_tab' => '1',
                'contact_raw' => '+62 851-0000-0999 Rini',
                'respond_time' => '0',
                'imported_cs_name' => 'Sisil',
                'imported_keterangan' => 'Tanya-Tanya',
                'raw_kebutuhan' => 'Cincin nikah',
                'greeting' => 'Halo Rini',
                'chat_time' => '10.46',
                'source_description' => 'Organik',
                'campaign_description' => 'Other',
                'imported_definition' => 'Warm',
            ]],
            'missing_source_fields' => 0,
            'errors' => ['Tab 2: Header lead utama pada tab 2 belum lengkap.'],
        ]);

        $summary = $sync->sync($cs->id, $branchId);

        $this->assertSame(1, $summary['inserted']);
        $this->assertSame(1, $summary['partial']);
        $this->assertDatabaseHas('lead_sync_runs', ['branch_id' => $branchId, 'status' => 'partial']);
        $this->assertDatabaseHas('branches', ['id' => $branchId, 'google_sheets_sync_error' => 'Tab 2: Header lead utama pada tab 2 belum lengkap.']);
    }

    public function test_google_sheet_sync_uses_primary_columns_when_follow_up_columns_repeat_headers(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet-palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $header = ['No', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting', 'Waktu Chat', 'Keterangan Sumber', 'Keterangan Campaign', 'Definisi Leads'];
        $neighbor = ['', 'No', 'Nama/Nomor', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting', 'Waktu Chat', 'Keterangan Sumber', 'Keterangan Campaign', 'Definisi Leads'];
        Http::fake(fn (Request $request) => str_contains($request->url(), 'fields=sheets.properties')
            ? Http::response(['properties' => ['title' => 'NOVEMBER 2026'], 'sheets' => [['properties' => ['title' => '15']]]])
            : Http::response(['valueRanges' => [['range' => "'15'!A1:AZ10", 'values' => [[], [], [...array_fill(0, 15, ''), ...$header, ...$neighbor], [...array_fill(0, 15, ''), '1', '081234567890', '0', 'Dilla', 'Tanya', 'Cincin', 'Halo', '10.30', 'Iklan', 'Bio', 'Warm', '', '2', '081299999999', 'Lain']]]]]));
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');
        $summary = $sync->sync(null, $branchId);
        $this->assertSame(1, $summary['inserted']);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567890', 'assigned_user_id' => $cs->id, 'imported_cs_name' => 'Dilla', 'lead_created_on' => '2026-11-15', 'chat_time' => '10.30']);
        $this->assertDatabaseMissing('leads', ['contact_key' => 'phone:6281299999999']);
    }

    public function test_google_sheet_sync_retries_a_temporary_failure_and_reads_headers_beyond_column_z(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Solo', 'google_spreadsheet_id' => 'sheet-solo']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Solo']);
        $otherCs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Kedua']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $cs->id]);
        config(['leads.google_sheets.credentials_path' => __FILE__]);

        $headers = ['No', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting', 'Waktu Chat', 'Keterangan Sumber', 'Keterangan Campaign', 'Definisi Leads'];
        $batchRequests = 0;
        Http::fake(function (Request $request) use (&$batchRequests, $headers) {
            if (str_contains($request->url(), 'fields=sheets.properties')) {
                return Http::response(['properties' => ['title' => 'Monitoring Leads NOVEMBER 2026'], 'sheets' => [['properties' => ['title' => '1']]]]);
            }
            $batchRequests++;
            if ($batchRequests === 1) {
                return Http::response([], 503);
            }

            return Http::response(['valueRanges' => [[
                'range' => "'1'!A1:AZ10",
                'values' => [
                    [],
                    ['', '26 November 2026'],
                    [...array_fill(0, 26, ''), 'Whatsapp 1'],
                    [...array_fill(0, 26, ''), ...$headers],
                    [...array_fill(0, 26, ''), '1', '+62 851-0000-0888 Rini', '0', 'Sisil', 'Tanya-Tanya', 'Cincin nikah', 'Halo Rini', '10.46', 'Organik', 'Other', 'Warm'],
                ],
            ]]]);
        });

        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');

        $summary = $sync->sync($cs->id, $branchId);

        $this->assertSame(1, $summary['inserted']);
        $this->assertSame(2, $batchRequests);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285100000888', 'greeting' => 'Halo Rini', 'lead_created_on' => '2026-11-01']);
        foreach ([$cs, $otherCs] as $account) {
            $this->actingAs($account)->get('/leads')->assertInertia(fn (Assert $page) => $page
                ->component('Leads/Index')
                ->where('leads.data.0.imported_cs_name', 'Sisil')
                ->where('leads.data.0.chat_time', '10.46')
                ->where('leads.data.0.lead_created_on', '2026-11-01'));
        }
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
        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'current_stage' => 'closing_repeat',
            'outcome' => null,
        ]);
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

    public function test_closing_outcome_keeps_the_closing_repeat_milestone_active(): void
    {
        $cs = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285112345699',
            'contact_key' => 'phone:6285112345699',
            'assigned_user_id' => $cs->id,
            'outcome' => 'closing',
            'outcome_factor' => 'offline_experience',
            'revision' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('lead_milestones')->insert([
            'lead_id' => $leadId,
            'milestone' => 'closing_repeat',
            'is_active' => true,
            'marked_at' => now(),
        ]);

        $this->actingAs($cs)->from('/leads')->patch("/leads/{$leadId}", [
            'inline' => true,
            'revision' => 0,
            'milestones' => ['offline'],
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'current_stage' => 'closing_repeat',
        ]);
        $this->assertDatabaseHas('lead_milestones', [
            'lead_id' => $leadId,
            'milestone' => 'closing_repeat',
            'is_active' => true,
        ]);
    }

    public function test_loss_outcome_deactivates_the_closing_repeat_milestone(): void
    {
        $cs = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285112345700',
            'contact_key' => 'phone:6285112345700',
            'assigned_user_id' => $cs->id,
            'outcome' => 'closing',
            'outcome_factor' => 'offline_experience',
            'current_stage' => 'closing_repeat',
            'revision' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('lead_milestones')->insert([
            'lead_id' => $leadId,
            'milestone' => 'closing_repeat',
            'is_active' => true,
            'marked_at' => now(),
        ]);

        $this->actingAs($cs)->from('/leads')->patch("/leads/{$leadId}", [
            'inline' => true,
            'revision' => 0,
            'outcome' => 'loss',
            'outcome_factor' => 'ghosting',
        ])->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'outcome' => 'loss',
            'current_stage' => null,
        ]);
        $this->assertDatabaseHas('lead_milestones', [
            'lead_id' => $leadId,
            'milestone' => 'closing_repeat',
            'is_active' => false,
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
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);

        $response = $this->actingAs($admin)->post('/leads', [
            'contact_raw' => '+62 851-7777-2222',
            'source_branch_id' => $branchId,
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
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);

        $this->actingAs($admin)->post('/leads', ['contact_raw' => '0812-3456-7890', 'source_branch_id' => $branchId])->assertRedirect();
        $this->actingAs($admin)->post('/leads', ['contact_raw' => '+62 812 3456 7890', 'source_branch_id' => $branchId])
            ->assertSessionHasErrors('contact_raw');

        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567890']);
    }

    public function test_international_phone_format_does_not_duplicate_a_legacy_local_phone_key(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        \DB::table('leads')->insert([
            'contact_raw' => '0812-3456-7890',
            'contact_key' => 'phone:081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->post('/leads', ['contact_raw' => '+62 812 3456 7890', 'source_branch_id' => $branchId])
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

        $this->writeDailyLeadSheet($writer, '1', '26 November 2026', '+62 851-7777-4444', '');
        $writer->addNewSheetAndMakeItCurrent();
        $this->writeDailyLeadSheet($writer, '2', '26 November 2026', '+62 851-7777-5555', '');
        $writer->addNewSheetAndMakeItCurrent()->setName('All Leads');
        $writer->addRow(Row::fromValues(['Tanggal', 'Nama/Nomor']));
        $writer->addRow(Row::fromValues(['1', '+62 851-7777-6666']));
        $writer->close();

        $response = $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => new UploadedFile($path, 'monitoring NOVEMBER 2026.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
            'import_mode' => 'daily_tabs',
            'branch_id' => $branchId,
        ]);

        $response->assertRedirect('/leads');
        $this->assertDatabaseCount('leads', 2);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177774444', 'source_date_raw' => '2026-11-01', 'lead_created_on' => '2026-11-01', 'source_branch_id' => $branchId, 'assigned_user_id' => $cs->id]);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177775555', 'source_date_raw' => '2026-11-02']);
        $this->assertDatabaseHas('lead_activity', ['action' => 'Diimpor dari tab harian', 'detail' => 'Sumber: tab 1 (2026-11-01). Nama CS sumber kosong; ditugaskan sementara ke CS Yogyakarta (Yogyakarta).']);
        $this->assertDatabaseMissing('leads', ['contact_key' => 'phone:6285177776666']);
        @unlink($path);
    }

    public function test_daily_xlsx_uses_earliest_numeric_tab_even_if_workbook_order_is_reversed(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $path = tempnam(sys_get_temp_dir(), 'daily-order-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $this->writeDailyLeadSheet($writer, '13', '13 November 2026', '081234567890', 'Dilla');
        $writer->addNewSheetAndMakeItCurrent();
        $this->writeDailyLeadSheet($writer, '1', '1 November 2026', '081234567890', 'Dilla');
        $writer->close();
        $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => new UploadedFile($path, 'monitoring NOVEMBER 2026.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
            'import_mode' => 'daily_tabs', 'branch_id' => $branchId,
        ])->assertRedirect('/leads');
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567890', 'lead_created_on' => '2026-11-01']);
        @unlink($path);
    }

    public function test_daily_xlsx_import_keeps_valid_tabs_when_another_tab_is_incomplete(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Yogyakarta']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $cs->id]);
        $path = tempnam(sys_get_temp_dir(), 'daily-partial-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $this->writeDailyLeadSheet($writer, '1', '1 November 2026', '+62 851-7777-7001');
        $writer->addNewSheetAndMakeItCurrent()->setName('2');
        $writer->addRow(Row::fromValues([null, '2 November 2026']));
        $writer->addRow(Row::fromValues(['No', 'Nama/Nomor', 'Respond Time']));
        $writer->close();

        $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => new UploadedFile($path, 'daily-partial.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
            'import_mode' => 'daily_tabs',
            'branch_id' => $branchId,
        ])->assertRedirect('/leads')
            ->assertSessionHas('inertia.flash_data.toast.type', 'warning');

        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177777001']);
        @unlink($path);
    }

    public function test_daily_xlsx_with_formula_date_and_unknown_period_does_not_guess_a_date(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $path = tempnam(sys_get_temp_dir(), 'daily-formula-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('1');
        $writer->addRow(Row::fromValues([null, 'Monitoring Leads']));
        $writer->addRow(new Row([Cell::fromValue(null), new FormulaCell('TODAY()', '26 November 2026')]));
        $writer->addRow(Row::fromValues(['Whatsapp 1']));
        $writer->addRow(Row::fromValues(['No', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting', 'Waktu Chat', 'Keterangan Sumber', 'Keterangan Campaign', 'Definisi Leads']));
        $writer->addRow(Row::fromValues(['1', '+62 851-7777-7002', '0', 'Dilla', 'klik saja', '', '', '', '', '', 'Cold']));
        $writer->close();

        $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => new UploadedFile($path, 'daily.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
            'import_mode' => 'daily_tabs', 'branch_id' => $branchId,
        ])->assertRedirect('/leads');
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177777002', 'assigned_user_id' => $cs->id, 'lead_created_on' => null]);
        @unlink($path);
    }

    public function test_daily_xlsx_with_empty_source_cs_requires_an_active_fallback(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $path = tempnam(sys_get_temp_dir(), 'daily-no-fallback-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $this->writeDailyLeadSheet($writer, '1', '1 November 2026', '+62 851-7777-7003', '');
        $writer->close();

        $this->actingAs($admin)->post('/leads/import', [
            'csv_file' => new UploadedFile($path, 'daily.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
            'import_mode' => 'daily_tabs', 'branch_id' => $branchId,
        ])->assertSessionHasErrors('branch_id');
        $this->assertDatabaseCount('leads', 0);
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
        $neighbor = ['', 'No', 'Nama/Nomor', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting', 'Waktu Chat', 'Keterangan Sumber', 'Keterangan Campaign', 'Definisi Leads'];
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), $headers, $neighbor)));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['1', '+62 851-7777-7111', '0', 'Dila', 'klik saja', '', 'Halo pertama', '10.38', 'Iklan', 'Bio', 'Cold'])));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['Whatsapp 2'])));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), $headers, $neighbor)));
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
                ->where('leads.total', 0),
        );

        $this->actingAs($admin)->get("/leads?cs_user_id={$otherCs->id}")->assertInertia(
            fn (Assert $page) => $page
                ->component('Leads/Index')
                ->where('leads.total', 1)
                ->where('leads.data.0.cs_filter_match', 'CS utama'),
        );
    }

    public function test_invalid_lead_date_filter_is_rejected_safely(): void
    {
        $cs = $this->user('cs');

        $this->actingAs($cs)
            ->get('/leads?lead_date_from=not-a-date')
            ->assertSessionHasErrors('lead_date_from');
    }

    public function test_admin_can_set_missing_source_branch_without_changing_existing_assignment(): void
    {
        $admin = $this->user('admin');
        $branch = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branch]);
        $lead = \DB::table('leads')->insertGetId(['contact_raw' => '081234567890']);
        $legacy = \DB::table('leads')->insertGetId(['contact_raw' => '081234567891', 'assigned_user_id' => $cs->id]);
        $this->actingAs($cs)->post('/leads/source-branch', ['lead_ids' => [$lead], 'source_branch_id' => $branch])->assertForbidden();
        $this->actingAs($admin)->post('/leads/source-branch', ['lead_ids' => [$lead, $legacy], 'source_branch_id' => $branch])->assertRedirect();
        $this->assertDatabaseHas('leads', ['id' => $lead, 'source_branch_id' => $branch, 'assigned_user_id' => null]);
        $this->assertDatabaseHas('leads', ['id' => $legacy, 'source_branch_id' => $branch, 'assigned_user_id' => $cs->id]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $lead, 'actor_id' => $admin->id, 'action' => 'Cabang sumber ditetapkan']);
        $this->actingAs($cs)->patch("/leads/{$lead}", ['inline' => true, 'keterangan' => 'Follow-up'])->assertRedirect();
        $this->actingAs($admin)->post('/leads/source-branch', ['lead_ids' => [$lead], 'source_branch_id' => $branch])->assertSessionHasErrors('lead_ids');
    }

    public function test_source_branch_selection_rejects_mixed_or_archived_leads_without_partial_updates(): void
    {
        $admin = $this->user('admin');
        $branch = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $empty = \DB::table('leads')->insertGetId(['contact_raw' => 'one']);
        $existing = \DB::table('leads')->insertGetId(['contact_raw' => 'two', 'source_branch_id' => $branch]);
        $archived = \DB::table('leads')->insertGetId(['contact_raw' => 'three', 'archived_at' => now()]);
        foreach ([$existing, $archived] as $ineligible) {
            $this->actingAs($admin)->post('/leads/source-branch', ['lead_ids' => [$empty, $ineligible], 'source_branch_id' => $branch])->assertSessionHasErrors('lead_ids');
            $this->assertDatabaseHas('leads', ['id' => $empty, 'source_branch_id' => null]);
        }
    }

    public function test_manual_assignment_is_rejected_without_changing_the_lead(): void
    {
        $admin = $this->user('admin');
        $cs = $this->user('cs');
        $leadId = \DB::table('leads')->insertGetId(['contact_raw' => '+6285200000000']);
        $this->actingAs($admin)->patch("/leads/{$leadId}", ['assigned_user_id' => $cs->id])->assertSessionHasErrors('assigned_user_id');
        $this->actingAs($admin)->post('/leads/assign', ['lead_ids' => [$leadId], 'assigned_user_id' => $cs->id])->assertStatus(410);
        $this->assertDatabaseHas('leads', ['id' => $leadId, 'assigned_user_id' => null]);
    }

    public function test_admin_creates_manual_lead_for_a_branch_team(): void
    {
        $admin = $this->user('admin');
        $branch = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branch]);
        $other = $this->user('cs', ['branch_id' => $branch]);
        $outsider = $this->user('cs', ['branch_id' => \DB::table('branches')->insertGetId(['name' => 'Solo'])]);
        $this->actingAs($admin)->post('/leads', ['contact_raw' => '081234567890'])->assertSessionHasErrors('source_branch_id');
        $this->actingAs($admin)->post('/leads', ['contact_raw' => '081234567890', 'source_branch_id' => $branch])->assertRedirect();
        $leadId = (int) \DB::table('leads')->where('contact_raw', '081234567890')->value('id');
        $this->assertDatabaseHas('leads', ['id' => $leadId, 'source_branch_id' => $branch, 'assigned_user_id' => null]);
        $this->actingAs($cs)->patch("/leads/{$leadId}", ['inline' => true, 'keterangan' => 'Follow-up pertama'])->assertRedirect();
        $this->actingAs($other)->patch("/leads/{$leadId}", ['inline' => true, 'keterangan' => 'Follow-up kedua'])->assertRedirect();
        $this->actingAs($outsider)->patch("/leads/{$leadId}", ['inline' => true, 'keterangan' => 'Luar cabang'])->assertNotFound();
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

    public function test_leads_are_ordered_by_source_date_then_id_with_missing_dates_last(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $admin = $this->user('admin');
        $cs = $this->user('cs', ['branch_id' => $branchId]);
        $insert = fn (?string $date, string $updated) => \DB::table('leads')->insertGetId(['source_branch_id' => $branchId, 'lead_created_on' => $date, 'updated_at' => $updated]);
        $missing = $insert(null, '2026-11-29 00:00:00');
        $old = $insert('2026-11-02', '2026-11-30 00:00:00');
        $new1 = $insert('2026-11-23', '2026-11-01 00:00:00');
        $new2 = $insert('2026-11-23', '2026-11-01 00:00:00');
        foreach ([$admin, $cs] as $viewer) {
            $this->actingAs($viewer)->get('/leads')->assertInertia(fn (Assert $page) => $page
                ->where('leads.data.0.id', $new2)->where('leads.data.1.id', $new1)
                ->where('leads.data.2.id', $old)->where('leads.data.3.id', $missing));
        }
    }

    public function test_admin_import_from_branches_returns_to_source_management(): void
    {
        $admin = $this->user('admin');
        $csv = "No,Nama/Nomor,Respond Time,Nama CS,Keterangan,Kebutuhan,Greeting*,Waktu Chat,Keterangan Sumber*,Keterangan Campaign*,Definisi Leads\n1,081234567890,0,Dilla,klik saja,nikah,halo,10.00,Iklan,Bio,Cold";
        $this->actingAs($admin)->from('/branches')->post('/leads/import', [
            'csv_file' => UploadedFile::fake()->createWithContent('leads.csv', $csv), 'return_to' => 'branches',
        ])->assertRedirect('/branches');
        $this->assertDatabaseHas('leads', ['contact_raw' => '081234567890']);
    }

    public function test_sync_all_from_branches_keeps_admin_on_source_management(): void
    {
        $admin = $this->user('admin');
        $cs = $this->user('cs');
        $sync = Mockery::mock(GoogleSheetSyncService::class);
        $sync->shouldReceive('sync')->once()->with($admin->id)->andReturn([
            'failed' => 0, 'partial' => 0, 'inserted' => 0, 'enriched' => 0, 'resurfaced' => 0,
            'missing_dates' => 0, 'chat_time_review' => 0, 'missing_source_fields' => 0, 'awaiting_account' => 0,
        ]);
        $this->app->instance(GoogleSheetSyncService::class, $sync);
        $this->actingAs($cs)->post('/leads/sync-google-sheet', ['return_to' => 'branches'])->assertForbidden();
        $this->actingAs($admin)->post('/leads/sync-google-sheet', ['return_to' => 'branches'])->assertRedirect('/branches');
    }

    public function test_lead_list_returns_every_matching_lead_without_pagination(): void
    {
        $admin = $this->user('admin');

        foreach (range(1, 21) as $number) {
            \DB::table('leads')->insert([
                'contact_raw' => sprintf('+628510000%04d', $number),
                'contact_key' => sprintf('phone:628510000%04d', $number),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($admin)->get('/leads')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Leads/Index')
                ->where('leads.total', 21)
                ->has('leads.data', 21),
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

    public function test_inline_lead_autosave_partial_reload_does_not_return_the_lead_list(): void
    {
        $admin = $this->user('admin');
        \DB::table('leads')->insert([
            'contact_raw' => '+6285100000997',
            'contact_key' => 'phone:6285100000997',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $initialResponse = $this->actingAs($admin)->get('/leads');
        $initialResponse->assertInertia(
            fn (Assert $page) => $page->component('Leads/Index'),
        );
        $version = $initialResponse->inertiaPage()['version'];

        $this->get('/leads', [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $version,
            'X-Inertia-Partial-Component' => 'Leads/Index',
            'X-Inertia-Partial-Data' => 'flash',
        ])
            ->assertOk()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonMissingPath('props.leads');
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
        app(LeadImportService::class)->import($rows, null, 'sheet-utama');

        $this->assertSame(['enriched' => 0, 'inserted' => 1, 'skipped' => 0, 'resurfaced' => 0, 'unassigned' => 1, 'awaiting_account' => 0, 'needs_review' => 0, 'missing_dates' => 0], $first);
        $this->assertSame(['enriched' => 0, 'inserted' => 0, 'skipped' => 1, 'resurfaced' => 0, 'unassigned' => 0, 'awaiting_account' => 0, 'needs_review' => 0, 'missing_dates' => 0], $again);
        $this->assertSame(['enriched' => 0, 'inserted' => 0, 'skipped' => 0, 'resurfaced' => 1, 'unassigned' => 0, 'awaiting_account' => 0, 'needs_review' => 0, 'missing_dates' => 0], $reappeared);
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177771999', 'lead_created_on' => '2026-11-01']);
        $this->assertDatabaseCount('lead_source_occurrences', 2);
        $this->assertDatabaseHas('lead_activity', ['action' => 'Lead muncul kembali dari Google Sheets']);
    }

    public function test_an_earlier_source_tab_does_not_automatically_reconcile_an_existing_lead(): void
    {
        $row = [
            'source_row_no' => '1',
            'source_date_raw' => '2 November 2026',
            'source_tab' => '2',
            'contact_raw' => '+62 851-7777-1997',
            'imported_cs_name' => 'Nanda',
        ];
        app(LeadImportService::class)->import([$row], null, 'sheet-utama');
        $row['source_date_raw'] = '1 November 2026';
        $row['source_tab'] = '1';
        $row['imported_cs_name'] = 'Dilla';
        app(LeadImportService::class)->import([$row], null, 'sheet-utama');

        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771997',
            'lead_created_on' => '2026-11-02',
            'imported_cs_name' => 'Nanda',
        ]);
    }

    public function test_sync_does_not_reconcile_historical_date_or_source_cs_automatically(): void
    {
        $row = [
            'source_row_no' => '1',
            'source_date_raw' => '26 November 2026',
            'source_tab' => '13',
            'contact_raw' => '+62 851-7777-1996',
            'imported_cs_name' => 'Nama lama',
        ];
        app(LeadImportService::class)->import([$row], null, 'sheet-utama');
        $row['source_date_raw'] = '13 November 2026';
        $row['imported_cs_name'] = 'Nama baru';
        app(LeadImportService::class)->import([$row], null, 'sheet-utama');

        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771996',
            'lead_created_on' => '2026-11-26',
            'imported_cs_name' => 'Nama lama',
        ]);
    }

    public function test_reconciliation_preview_compares_source_without_changing_work(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $row = [
            'source_row_no' => '1', 'source_tab' => '13', 'source_date_raw' => '26 November 2026',
            'contact_raw' => '+62 851-7777-1995', 'imported_cs_name' => 'Dilla',
        ];
        $importer = app(LeadImportService::class);
        $importer->import([$row], null, 'sheet-palagan', $branchId);
        $row['source_date_raw'] = '13 November 2026';
        $row['imported_cs_name'] = 'Nanda';

        $preview = $importer->previewReconciliation([$row], $branchId);

        $this->assertCount(1, $preview['changes']);
        $this->assertSame('2026-11-13', $preview['changes'][0]['new_date']);
        $this->assertSame('Nanda', $preview['changes'][0]['new_cs']);
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771995', 'lead_created_on' => '2026-11-26',
            'assigned_user_id' => $cs->id, 'imported_cs_name' => 'Dilla',
        ]);
    }

    public function test_reconciliation_pairs_chat_time_with_first_source_tab(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $importer = app(LeadImportService::class);
        $first = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla', 'chat_time' => '09.17'];
        $later = [...$first, 'source_tab' => '13', 'source_date_raw' => '2026-11-13', 'chat_time' => '16.56'];
        $importer->import([$first], null, 'sheet', $branchId);
        \DB::table('leads')->where('contact_key', 'phone:6281234567890')->update(['lead_created_on' => null, 'chat_time' => '16.56']);
        $preview = $importer->previewReconciliation([$later, $first], $branchId);
        $this->assertSame('09.17', $preview['changes'][0]['new_chat_time']);
        $importer->applyReconciliation($preview['changes'], $branchId, $cs->id);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567890', 'lead_created_on' => '2026-11-01', 'chat_time' => '09.17']);
    }

    public function test_later_source_tab_does_not_overwrite_first_chat_time(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $importer = app(LeadImportService::class);
        $first = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla', 'chat_time' => '09.17'];
        $later = [...$first, 'source_tab' => '13', 'source_date_raw' => '2026-11-13', 'chat_time' => '16.56'];
        $importer->import([$first, $later], null, 'sheet', $branchId);
        $importer->import([$later], null, 'sheet', $branchId);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567890', 'lead_created_on' => '2026-11-01', 'chat_time' => '09.17']);
    }

    public function test_reconciliation_retains_chat_time_when_first_day_is_ambiguous(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $importer = app(LeadImportService::class);
        $first = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla', 'chat_time' => '09.17'];
        $importer->import([$first], null, 'sheet', $branchId);
        $preview = $importer->previewReconciliation([$first, [...$first, 'source_row_no' => '2', 'chat_time' => '16.56']], $branchId);
        $this->assertSame([], $preview['changes']);
        $this->assertSame(0, $preview['matched']);
        $this->assertStringContainsString('Waktu chat', $preview['review'][0]['reason']);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567890', 'chat_time' => '09.17']);
    }

    public function test_reconciliation_marks_missing_first_chat_time_for_review(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $importer = app(LeadImportService::class);
        $row = ['source_tab' => '13', 'source_row_no' => '1', 'source_date_raw' => '2026-11-13', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla', 'chat_time' => '16.56'];
        $importer->import([$row], null, 'sheet', $branchId);
        $preview = $importer->previewReconciliation([[...$row, 'source_tab' => '1', 'source_date_raw' => '2026-11-01', 'chat_time' => '']], $branchId);
        $this->assertSame('16.56', $preview['changes'][0]['new_chat_time']);
        $this->assertStringContainsString('Waktu chat', $preview['review'][0]['reason']);
        $this->assertSame(0, $preview['matched']);
    }

    public function test_google_sheet_sync_reports_changed_first_chat_time_for_admin_review(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $row = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla', 'chat_time' => ''];
        app(LeadImportService::class)->import([$row], null, 'sheet', $branchId);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');
        $sync->shouldReceive('readBranch')->once()->andReturn(['rows' => [[...$row, 'chat_time' => '09.17']], 'missing_source_fields' => 0, 'errors' => []]);
        $summary = $sync->sync(null, $branchId);
        $this->assertSame(1, $summary['chat_time_review']);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567890', 'chat_time' => '']);
    }

    public function test_google_sheet_sync_reports_conflicting_chat_times_on_first_import(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $row = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla', 'chat_time' => '09.17'];
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');
        $sync->shouldReceive('readBranch')->once()->andReturn(['rows' => [$row, [...$row, 'source_row_no' => '2', 'chat_time' => '16.56']], 'missing_source_fields' => 0, 'errors' => []]);
        $this->assertSame(1, $sync->sync(null, $branchId)['chat_time_review']);
    }

    public function test_active_source_preview_reports_new_and_existing_rows_without_importing(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        $row = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        app(LeadImportService::class)->import([$row], null, 'sheet', $branchId);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');
        $sync->shouldReceive('readBranch')->once()->andReturn(['rows' => [$row, [...$row, 'contact_raw' => '081234567891']], 'missing_source_fields' => 0, 'errors' => []]);

        $plan = $sync->previewReconciliation($branchId);

        $this->assertSame(1, $plan['source_comparison']['matching']);
        $this->assertSame(1, $plan['source_comparison']['new']);
        $this->assertSame(1, \DB::table('leads')->count());
    }

    public function test_reconciliation_reports_missing_leads_and_protects_older_dates(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $importer = app(LeadImportService::class);
        $old = ['source_tab' => '15', 'source_date_raw' => '2026-10-15', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        $missing = [...$old, 'contact_raw' => '081234567891'];
        $importer->import([$old, $missing], null, 'sheet', $branchId);
        $preview = $importer->previewReconciliation([[...$old, 'source_tab' => '1', 'source_date_raw' => '2026-11-01']], $branchId);
        $this->assertSame([], $preview['changes']);
        $this->assertCount(2, $preview['review']);
    }

    public function test_reconciliation_identifies_missing_imports_without_including_manual_leads(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $importer = app(LeadImportService::class);
        $row = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        $missing = [...$row, 'source_row_no' => '2', 'contact_raw' => '081234567891'];
        $importer->import([$row, $missing], null, 'sheet-lama', $branchId);
        \DB::table('leads')->insert(['source_branch_id' => $branchId, 'contact_key' => 'phone:6281234567892', 'contact_raw' => '081234567892']);
        $preview = $importer->previewReconciliation([$row], $branchId);
        $this->assertCount(1, $preview['archive_candidates']);
        $this->assertSame('081234567891', $preview['archive_candidates'][0]['contact']);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567892', 'archived_at' => null]);
        $admin = $this->user('admin');
        $this->assertSame(1, $importer->archiveMissing($preview['archive_candidates'], $branchId, $admin->id));
        $this->assertNotNull(\DB::table('leads')->where('contact_key', 'phone:6281234567891')->value('archived_at'));
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567892', 'archived_at' => null]);
        $this->assertDatabaseHas('lead_activity', ['actor_id' => $admin->id, 'action' => 'Lead diarsipkan']);
        $after = $importer->previewReconciliation([$row], $branchId);
        $this->assertSame(1, $after['missing']);
        $this->assertSame([], $after['archive_candidates']);
    }

    public function test_archive_missing_rejects_changed_leads_without_partial_archiving(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $admin = $this->user('admin');
        $importer = app(LeadImportService::class);
        $row = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        $importer->import([$row, [...$row, 'source_row_no' => '2', 'contact_raw' => '081234567891']], null, 'sheet', $branchId);
        $preview = $importer->previewReconciliation([], $branchId);
        \DB::table('leads')->where('id', $preview['archive_candidates'][1]['lead_id'])->update(['revision' => 1]);
        try {
            $importer->archiveMissing($preview['archive_candidates'], $branchId, $admin->id);
            $this->fail('Pratinjau yang kedaluwarsa harus ditolak.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('berubah setelah pratinjau', $exception->getMessage());
        }
        $this->assertSame(0, \DB::table('leads')->whereNotNull('archived_at')->count());
    }

    public function test_admin_can_archive_missing_imports_from_a_current_preview_and_restore_them(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $row = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        $missing = [...$row, 'source_row_no' => '2', 'contact_raw' => '081234567891'];
        app(LeadImportService::class)->import([$row, $missing], null, 'sheet', $branchId);
        \DB::table('leads')->insert(['source_branch_id' => $branchId, 'contact_key' => 'phone:6281234567892', 'contact_raw' => '081234567892']);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->twice()->andReturn('token');
        $sync->shouldReceive('readBranch')->twice()->andReturn(['rows' => [$row], 'missing_source_fields' => 0, 'errors' => [], 'spreadsheet_title' => 'November 2026']);
        $this->app->instance(GoogleSheetSyncService::class, $sync);
        $this->actingAs($cs)->post("/branches/{$branchId}/archive-missing-leads")->assertForbidden();
        $this->actingAs($admin)->post("/branches/{$branchId}/preview-reconciliation")->assertRedirect();
        $this->actingAs($admin)->post("/branches/{$branchId}/archive-missing-leads", ['backup_confirmed' => true, 'period_confirmed' => true])->assertSessionHasErrors('source_coverage_confirmed');
        $this->actingAs($admin)->post("/branches/{$branchId}/archive-missing-leads", ['backup_confirmed' => true, 'period_confirmed' => true, 'source_coverage_confirmed' => true])->assertRedirect();
        $id = (int) \DB::table('leads')->where('contact_key', 'phone:6281234567891')->value('id');
        $this->assertNotNull(\DB::table('leads')->where('id', $id)->value('archived_at'));
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567892', 'archived_at' => null]);
        $this->actingAs($admin)->patch("/leads/{$id}/restore")->assertRedirect();
        $this->assertDatabaseHas('leads', ['id' => $id, 'archived_at' => null]);
    }

    public function test_archive_missing_refuses_empty_or_failed_source(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        $row = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        app(LeadImportService::class)->import([$row], null, 'sheet', $branchId);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->times(4)->andReturn('token');
        $empty = ['rows' => [], 'missing_source_fields' => 0, 'errors' => [], 'spreadsheet_title' => 'November 2026'];
        $failed = ['rows' => [[...$row, 'contact_raw' => '081234567891']], 'missing_source_fields' => 0, 'errors' => ['Tab gagal dibaca'], 'spreadsheet_title' => 'November 2026'];
        $sync->shouldReceive('readBranch')->times(4)->andReturn($empty, $empty, $failed, $failed);
        foreach ([0, 1] as $case) {
            $plan = $sync->previewReconciliation($branchId);
            try {
                $sync->archiveMissing($branchId, $admin->id, hash('sha256', json_encode($plan, JSON_THROW_ON_ERROR)));
                $this->fail('Sumber kosong atau tidak lengkap harus ditolak.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('Buat pratinjau baru', $exception->getMessage());
            }
        }
        $this->assertSame(0, \DB::table('leads')->whereNotNull('archived_at')->count());
    }

    public function test_archive_missing_rechecks_changed_spreadsheet_before_mutation(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet']);
        $row = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        app(LeadImportService::class)->import([$row], null, 'sheet', $branchId);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $sourceRows = [[...$row, 'contact_raw' => '081234567891']];
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->twice()->andReturn('token');
        $sync->shouldReceive('readBranch')->twice()->andReturnUsing(function () use (&$sourceRows) {
            return ['rows' => $sourceRows, 'missing_source_fields' => 0, 'errors' => [], 'spreadsheet_title' => 'November 2026'];
        });
        $plan = $sync->previewReconciliation($branchId);
        $sourceRows = [$row];
        try {
            $sync->archiveMissing($branchId, $admin->id, hash('sha256', json_encode($plan, JSON_THROW_ON_ERROR)));
            $this->fail('Pratinjau sumber yang berubah harus ditolak.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Buat pratinjau baru', $exception->getMessage());
        }
        $this->assertSame(0, \DB::table('leads')->whereNotNull('archived_at')->count());
    }

    public function test_reconciliation_can_correct_date_when_source_cs_is_missing(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $importer = app(LeadImportService::class);
        $row = ['source_tab' => '13', 'source_date_raw' => '2026-11-26', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        $importer->import([$row], null, 'sheet', $branchId);
        $preview = $importer->previewReconciliation([[...$row, 'source_date_raw' => '2026-11-13', 'imported_cs_name' => '']], $branchId);
        $this->assertSame('2026-11-13', $preview['changes'][0]['new_date']);
        $this->assertSame('Dilla', $preview['changes'][0]['new_cs']);
        $this->assertNotEmpty($preview['review']);
        $importer->applyReconciliation($preview['changes'], $branchId, $cs->id);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567890', 'lead_created_on' => '2026-11-13', 'imported_cs_name' => 'Dilla', 'assigned_user_id' => $cs->id]);
    }

    public function test_reconciliation_does_not_choose_between_duplicate_phone_formats(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        foreach (['phone:081234567890', 'phone:6281234567890'] as $key) {
            \DB::table('leads')->insert(['contact_key' => $key, 'contact_raw' => $key, 'source_branch_id' => $branchId]);
        }
        $row = ['source_tab' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        $preview = app(LeadImportService::class)->previewReconciliation([$row], $branchId);
        $this->assertSame([], $preview['changes']);
        $this->assertSame(1, $preview['uncertain']);
    }

    public function test_existing_row_without_cs_does_not_require_fallback(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $existing = ['source_tab' => '1', 'source_row_no' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        $importer = app(LeadImportService::class);
        $importer->import([$existing], null, 'sheet', $branchId);
        $new = [...$existing, 'source_row_no' => '2', 'contact_raw' => '081234567891'];
        $importer->import([[...$existing, 'imported_cs_name' => ''], $new], null, 'sheet', $branchId);
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6281234567891', 'assigned_user_id' => $cs->id]);
    }

    public function test_daily_tab_date_supports_numeric_month_and_year(): void
    {
        $this->assertSame('2026-11-13', LeadSourceDate::fromDailyTab('13', 'Monitoring 11-2026'));
        $this->assertNull(LeadSourceDate::fromDailyTab('13', 'Monitoring 11-2026 12-2026'));
    }

    public function test_reconciliation_applies_only_confirmed_source_fields(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $row = [
            'source_row_no' => '1', 'source_tab' => '13', 'source_date_raw' => '26 November 2026',
            'contact_raw' => '+62 851-7777-1994', 'imported_cs_name' => 'Dilla',
        ];
        $importer = app(LeadImportService::class);
        $importer->import([$row], null, 'sheet-palagan', $branchId);
        $row['source_date_raw'] = '13 November 2026';
        $row['imported_cs_name'] = 'Nanda';
        $preview = $importer->previewReconciliation([$row], $branchId);

        $this->assertSame(1, $importer->applyReconciliation($preview['changes'], $branchId, $cs->id));
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771994', 'lead_created_on' => '2026-11-13',
            'assigned_user_id' => $cs->id, 'imported_cs_name' => 'Nanda',
        ]);
        $this->assertDatabaseHas('lead_activity', ['actor_id' => $cs->id, 'action' => 'Data sumber direkonsiliasi']);
    }

    public function test_changed_source_cs_without_account_is_visible_for_admin_review(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $row = ['source_row_no' => '1', 'source_tab' => '1', 'source_date_raw' => '2026-11-01', 'contact_raw' => '081234567890', 'imported_cs_name' => 'Dilla'];
        $importer = app(LeadImportService::class);
        $importer->import([$row], null, 'sheet', $branchId);
        $preview = $importer->previewReconciliation([[...$row, 'imported_cs_name' => 'Nanda']], $branchId);
        $importer->applyReconciliation($preview['changes'], $branchId, $admin->id);
        $this->assertDatabaseHas('leads', ['assigned_user_id' => $cs->id, 'owner_resolution' => 'manual', 'imported_cs_name' => 'Nanda']);
        $this->actingAs($admin)->get('/accounts')->assertInertia(fn (Assert $page) => $page
            ->component('Accounts/Index')
            ->where('unmatched_source_names.0.name', 'Nanda')
            ->where('unmatched_source_names.0.count', 1)
            ->where('unmatched_source_names.0.branch_name', 'Palagan'));
    }

    public function test_reconciliation_rejects_a_stale_preview_without_overwriting_changes(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $row = [
            'source_row_no' => '1', 'source_tab' => '13', 'source_date_raw' => '26 November 2026',
            'contact_raw' => '+62 851-7777-1993', 'imported_cs_name' => 'Dilla',
        ];
        $importer = app(LeadImportService::class);
        $importer->import([$row], null, 'sheet-palagan', $branchId);
        $row['source_date_raw'] = '13 November 2026';
        $preview = $importer->previewReconciliation([$row], $branchId);
        \DB::table('leads')->where('id', $preview['changes'][0]['lead_id'])->update(['imported_cs_name' => 'Koreksi lain']);

        try {
            $importer->applyReconciliation($preview['changes'], $branchId, $cs->id);
            $this->fail('Pratinjau lama harus ditolak.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('berubah setelah pratinjau', $exception->getMessage());
        }
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177771993', 'lead_created_on' => '2026-11-26', 'imported_cs_name' => 'Koreksi lain']);
    }

    public function test_reconciliation_excludes_ambiguous_names_and_cross_branch_contacts(): void
    {
        $palagan = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $solo = \DB::table('branches')->insertGetId(['name' => 'Solo']);
        $row = ['source_row_no' => '1', 'source_tab' => '13', 'source_date_raw' => '13 November 2026', 'contact_raw' => '+62 851-7777-1990', 'imported_cs_name' => 'Dilla'];
        $other = [...$row, 'contact_raw' => '+62 851-7777-1991'];
        $shared = [...$row, 'contact_raw' => '+62 851-7777-1989'];
        $importer = app(LeadImportService::class);
        $importer->import([$row], null, 'sheet-palagan', $palagan);
        $importer->import([$other], null, 'sheet-solo', $solo);
        $importer->import([$shared], null, 'sheet-palagan', $palagan);
        $importer->import([$shared], null, 'sheet-solo', $solo);
        $ambiguous = [...$row, 'imported_cs_name' => 'Nanda'];

        $preview = $importer->previewReconciliation([$row, $ambiguous, $other, $shared], $palagan);

        $this->assertSame([], $preview['changes']);
        $this->assertSame(1, $preview['uncertain']);
        $this->assertSame(2, $preview['conflicts']);
    }

    public function test_only_admin_can_preview_and_apply_reconciliation_after_confirming_backup(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan', 'google_spreadsheet_id' => 'sheet-palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'Dilla']);
        $row = [
            'source_row_no' => '1', 'source_tab' => '13', 'source_date_raw' => '26 November 2026',
            'contact_raw' => '+62 851-7777-1992', 'imported_cs_name' => 'Dilla',
        ];
        app(LeadImportService::class)->import([$row], null, 'sheet-palagan', $branchId);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $headers = ['No', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting', 'Waktu Chat', 'Keterangan Sumber', 'Keterangan Campaign', 'Definisi Leads'];
        $sourceName = 'Nanda';
        Http::fake(function (Request $request) use ($headers, &$sourceName) {
            return str_contains($request->url(), 'fields=sheets.properties')
                ? Http::response(['properties' => ['title' => 'NOVEMBER 2026'], 'sheets' => [['properties' => ['title' => '13']]]])
                : Http::response(['valueRanges' => [['range' => "'13'!A1:AZ10", 'values' => [[], ['', '26 November 2026'], ['Whatsapp 1'], $headers, ['1', '+62 851-7777-1992', '0', $sourceName, 'Klik saja', '', '', '', '', '', 'Cold']]]]]);
        });
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])
            ->makePartial()->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->andReturn('token');
        $this->app->instance(GoogleSheetSyncService::class, $sync);

        $this->actingAs($cs)->post("/branches/{$branchId}/preview-reconciliation")->assertForbidden();
        $this->actingAs($admin)->from('/branches')->post("/branches/{$branchId}/preview-reconciliation")->assertRedirect('/branches');
        $this->assertCount(1, session('reconciliation_preview.changes'));
        $this->actingAs($cs)->post("/branches/{$branchId}/apply-reconciliation", ['backup_confirmed' => true])->assertForbidden();
        $this->actingAs($admin)->from('/branches')->post("/branches/{$branchId}/apply-reconciliation", [])->assertSessionHasErrors('backup_confirmed');
        $this->actingAs($admin)->from('/branches')->post("/branches/{$branchId}/apply-reconciliation", ['backup_confirmed' => true])->assertSessionHasErrors('period_confirmed');
        $sourceName = 'Nama berubah';
        $this->actingAs($admin)->from('/branches')->post("/branches/{$branchId}/apply-reconciliation", ['backup_confirmed' => true, 'period_confirmed' => true])->assertRedirect('/branches')
            ->assertSessionHas('inertia.flash_data.toast.type', 'error');
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177771992', 'lead_created_on' => '2026-11-26', 'imported_cs_name' => 'Dilla']);
        $sourceName = 'Nanda';
        $this->actingAs($admin)->from('/branches')->post("/branches/{$branchId}/preview-reconciliation")->assertRedirect('/branches');
        $this->actingAs($admin)->from('/branches')->post("/branches/{$branchId}/apply-reconciliation", ['backup_confirmed' => true, 'period_confirmed' => true])->assertRedirect('/branches');
        $this->assertDatabaseHas('leads', ['contact_key' => 'phone:6285177771992', 'lead_created_on' => '2026-11-13', 'imported_cs_name' => 'Nanda', 'assigned_user_id' => $cs->id]);
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

    public function test_replacing_a_branch_sheet_refreshes_matching_source_data_without_touching_cs_work(): void
    {
        $branchId = \DB::table('branches')->insertGetId([
            'name' => 'Palagan',
            'google_spreadsheet_id' => 'sheet-lama',
        ]);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Palagan']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $cs->id]);
        $row = [
            'source_row_no' => '1',
            'source_date_raw' => '1 November 2026',
            'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1779 Rini',
            'respond_time' => '0',
            'imported_cs_name' => 'Nama lama',
            'imported_keterangan' => 'Klik saja',
            'raw_kebutuhan' => 'Cincin nikah',
            'greeting' => 'Halo',
            'chat_time' => '10.38',
            'source_description' => 'Iklan lama',
            'campaign_description' => 'Bio lama',
            'imported_definition' => 'Cold',
        ];
        app(LeadImportService::class)->import([$row], null, 'sheet-lama', $branchId);
        $leadId = (int) \DB::table('leads')->where('contact_key', 'phone:6285177771779')->value('id');
        \DB::table('leads')->where('id', $leadId)->update([
            'keterangan' => 'Catatan follow-up CS',
            'current_stage' => 'asked',
        ]);
        $row['greeting'] = 'Halo dari master baru';
        $row['raw_kebutuhan'] = 'Cincin tunangan';
        $row['source_description'] = 'Iklan baru';
        $unmatchedRow = [...$row, 'source_row_no' => '2', 'contact_raw' => '+62 851-7777-1780 Sari'];
        app(LeadImportService::class)->import([$unmatchedRow], null, 'sheet-lama', $branchId);

        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');
        $sync->shouldReceive('readBranch')->once()->with(Mockery::type(PendingRequest::class), 'sheet-baru')->andReturn([
            'rows' => [$row],
            'missing_source_fields' => 0,
            'errors' => [],
        ]);

        $summary = $sync->replaceSource($cs->id, $branchId, 'sheet-baru');

        $this->assertSame(1, $summary['enriched']);
        $this->assertSame(0, $summary['inserted']);
        $this->assertDatabaseHas('branches', [
            'id' => $branchId,
            'google_spreadsheet_id' => 'sheet-baru',
        ]);
        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'greeting' => 'Halo dari master baru',
            'raw_kebutuhan' => 'Cincin tunangan',
            'source_description' => 'Iklan baru',
            'keterangan' => 'Catatan follow-up CS',
            'current_stage' => 'asked',
        ]);
        $this->assertDatabaseCount('leads', 2);
        $this->assertDatabaseCount('lead_source_occurrences', 3);
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771780',
            'source_branch_id' => $branchId,
        ]);
        $this->assertDatabaseHas('branch_source_events', [
            'branch_id' => $branchId,
            'actor_id' => $cs->id,
            'action' => 'replaced',
            'previous_spreadsheet_id' => 'sheet-lama',
            'spreadsheet_id' => 'sheet-baru',
        ]);
    }

    public function test_failed_branch_sheet_replacement_keeps_the_previous_source_active(): void
    {
        $branchId = \DB::table('branches')->insertGetId([
            'name' => 'Palagan',
            'google_spreadsheet_id' => 'sheet-lama',
            'google_sheets_synced_at' => now()->subHour(),
            'google_sheets_sync_error' => 'Catatan lama',
        ]);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Palagan']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $cs->id]);
        config(['leads.google_sheets.credentials_path' => __FILE__]);
        $sync = Mockery::mock(GoogleSheetSyncService::class, [app(LeadImportService::class), app(DailyLeadTabParser::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $sync->shouldReceive('accessToken')->once()->andReturn('token');
        $sync->shouldReceive('readBranch')->once()->with(Mockery::type(PendingRequest::class), 'sheet-baru')
            ->andThrow(new \RuntimeException('Spreadsheet baru tidak dapat dibaca.'));

        try {
            $sync->replaceSource($cs->id, $branchId, 'sheet-baru');
            $this->fail('Penggantian seharusnya gagal.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Sinkronisasi cabang gagal. Palagan: Spreadsheet baru tidak dapat dibaca.', $exception->getMessage());
        }

        $this->assertDatabaseHas('branches', [
            'id' => $branchId,
            'google_spreadsheet_id' => 'sheet-lama',
            'google_sheets_sync_error' => 'Catatan lama',
        ]);
        $this->assertDatabaseHas('branch_source_events', [
            'branch_id' => $branchId,
            'actor_id' => $cs->id,
            'action' => 'replacement_failed',
            'previous_spreadsheet_id' => 'sheet-lama',
            'spreadsheet_id' => 'sheet-baru',
        ]);
    }

    public function test_source_occurrence_keeps_the_importing_branch_when_the_contact_belongs_to_another_branch(): void
    {
        $palaganId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $soloId = \DB::table('branches')->insertGetId(['name' => 'Solo']);
        $palaganCs = $this->user('cs', ['branch_id' => $palaganId, 'name' => 'CS Palagan']);
        $soloCs = $this->user('cs', ['branch_id' => $soloId, 'name' => 'CS Solo']);
        \DB::table('branches')->where('id', $palaganId)->update(['default_import_user_id' => $palaganCs->id]);
        \DB::table('branches')->where('id', $soloId)->update(['default_import_user_id' => $soloCs->id]);
        $row = [
            'source_row_no' => '1',
            'source_date_raw' => '1 November 2026',
            'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1880 Rini',
            'respond_time' => '0',
            'imported_cs_name' => 'CS Solo',
            'imported_keterangan' => 'Klik saja',
            'raw_kebutuhan' => 'Cincin nikah',
            'greeting' => 'Halo',
            'chat_time' => '10.38',
            'source_description' => 'Iklan',
            'campaign_description' => 'Bio',
            'imported_definition' => 'Cold',
        ];
        app(LeadImportService::class)->import([$row], null, 'sheet-solo', $soloId);
        $row['imported_cs_name'] = 'CS Palagan';
        app(LeadImportService::class)->import([$row], null, 'sheet-palagan', $palaganId);
        app(LeadImportService::class)->import([$row], null, 'sheet-palagan', $palaganId);

        $this->assertDatabaseHas('lead_source_occurrences', [
            'spreadsheet_id' => 'sheet-palagan',
            'source_branch_id' => $palaganId,
        ]);
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771880',
            'source_branch_id' => $soloId,
            'imported_cs_name' => 'CS Solo',
        ]);
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

    public function test_import_from_a_branch_assigns_the_matching_source_cs_instead_of_the_fallback(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $sourceCs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Yogyakarta 1']);
        $fallbackCs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Yogyakarta 2']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $fallbackCs->id]);
        $rows = [[
            'source_row_no' => '1',
            'source_date_raw' => '1 November 2026',
            'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1888 Rini',
            'respond_time' => '0',
            'imported_cs_name' => 'CS Yogyakarta 1',
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
            'assigned_user_id' => $sourceCs->id,
            'owner_resolution' => 'source_matched',
        ]);
    }

    public function test_import_from_a_branch_waits_for_an_account_when_source_cs_is_not_registered(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $fallbackCs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Yogyakarta']);
        \DB::table('branches')->where('id', $branchId)->update(['default_import_user_id' => $fallbackCs->id]);
        $row = [
            'source_row_no' => '1', 'source_date_raw' => '1 November 2026', 'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1889 Rini', 'respond_time' => '0', 'imported_cs_name' => 'CS Baru',
            'imported_keterangan' => 'tanya-tanya', 'raw_kebutuhan' => 'cincin nikah', 'greeting' => 'Halo',
            'chat_time' => '10.38', 'source_description' => 'Iklan', 'campaign_description' => 'Bio', 'imported_definition' => 'Warm',
        ];

        $summary = app(LeadImportService::class)->import([$row], null, 'sheet-yogyakarta', $branchId);

        $this->assertSame(1, $summary['awaiting_account']);
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771889',
            'assigned_user_id' => null,
            'owner_resolution' => 'awaiting_account',
            'imported_cs_name' => 'CS Baru',
        ]);
        $this->assertDatabaseMissing('lead_collaborators', ['user_id' => $fallbackCs->id]);
    }

    public function test_import_with_a_source_cs_name_does_not_require_a_fallback_account(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $row = [
            'source_row_no' => '1', 'source_date_raw' => '1 November 2026', 'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1891 Rini', 'respond_time' => '0', 'imported_cs_name' => 'CS Baru',
            'imported_keterangan' => 'tanya-tanya', 'raw_kebutuhan' => 'cincin nikah', 'greeting' => 'Halo',
            'chat_time' => '10.38', 'source_description' => 'Iklan', 'campaign_description' => 'Bio', 'imported_definition' => 'Warm',
        ];

        $summary = app(LeadImportService::class)->import([$row], null, 'sheet-yogyakarta', $branchId);

        $this->assertSame(1, $summary['awaiting_account']);
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771891',
            'assigned_user_id' => null,
            'owner_resolution' => 'awaiting_account',
        ]);
    }

    public function test_import_matches_a_configured_source_cs_alias(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Yogyakarta']);
        \DB::table('cs_source_name_aliases')->insert([
            'branch_id' => $branchId,
            'user_id' => $cs->id,
            'source_name' => 'Yogyakarta 1',
            'source_name_key' => 'yogyakarta 1',
            'created_at' => now(),
        ]);
        $row = [
            'source_row_no' => '1', 'source_date_raw' => '1 November 2026', 'source_tab' => '1',
            'contact_raw' => '+62 851-7777-1892 Rini', 'respond_time' => '0', 'imported_cs_name' => 'Yogyakarta 1',
            'imported_keterangan' => 'tanya-tanya', 'raw_kebutuhan' => 'cincin nikah', 'greeting' => 'Halo',
            'chat_time' => '10.38', 'source_description' => 'Iklan', 'campaign_description' => 'Bio', 'imported_definition' => 'Warm',
        ];

        app(LeadImportService::class)->import([$row], null, 'sheet-yogyakarta', $branchId);

        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177771892',
            'assigned_user_id' => $cs->id,
            'owner_resolution' => 'source_matched',
        ]);
    }

    public function test_admin_can_link_awaiting_source_leads_to_a_matching_cs_account_without_changing_collaborators(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $cs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Baru']);
        $helper = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Pendamping']);
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285177771890', 'contact_key' => 'phone:6285177771890', 'source_branch_id' => $branchId,
            'imported_cs_name' => 'CS Baru', 'owner_resolution' => 'awaiting_account', 'created_at' => now(), 'updated_at' => now(),
        ]);
        \DB::table('lead_collaborators')->insert(['lead_id' => $leadId, 'user_id' => $helper->id, 'added_at' => now()]);

        $this->actingAs($admin)->post("/accounts/{$cs->id}/link-awaiting-leads")->assertRedirect();

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'assigned_user_id' => $cs->id, 'owner_resolution' => 'source_matched']);
        $this->assertDatabaseHas('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $helper->id, 'removed_at' => null]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'action' => 'CS utama dihubungkan dari Nama CS sumber']);
    }

    public function test_admin_can_reconcile_a_historical_lead_without_changing_its_collaborators(): void
    {
        $admin = $this->user('admin');
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Yogyakarta']);
        $targetCs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Baru']);
        $previousCs = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Lama']);
        $helper = $this->user('cs', ['branch_id' => $branchId, 'name' => 'CS Pendamping']);
        $leadId = \DB::table('leads')->insertGetId([
            'contact_raw' => '+6285177771893', 'contact_key' => 'phone:6285177771893', 'source_branch_id' => $branchId,
            'assigned_user_id' => $previousCs->id, 'imported_cs_name' => 'CS Baru', 'owner_resolution' => 'manual', 'created_at' => now(), 'updated_at' => now(),
        ]);
        \DB::table('lead_collaborators')->insert(['lead_id' => $leadId, 'user_id' => $helper->id, 'added_at' => now()]);

        $this->actingAs($admin)->post("/accounts/{$targetCs->id}/reconcile-source-leads")->assertRedirect();

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'assigned_user_id' => $targetCs->id, 'owner_resolution' => 'source_matched']);
        $this->assertDatabaseHas('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $helper->id, 'removed_at' => null]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'action' => 'CS utama direkonsiliasi dari Nama CS sumber']);
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
            'imported_cs_name' => '',
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

    public function test_current_stage_follows_the_highest_milestone(): void
    {
        $admin = $this->user('admin');
        $firstLead = \DB::table('leads')->insertGetId(['contact_raw' => '+6285400000001', 'contact_key' => 'phone:6285400000001', 'created_at' => now(), 'updated_at' => now()]);

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

    public function test_cs_cannot_manage_archives_even_for_their_own_leads(): void
    {
        $cs = $this->user('cs');
        $active = \DB::table('leads')->insertGetId(['assigned_user_id' => $cs->id, 'contact_raw' => 'active']);
        $archived = \DB::table('leads')->insertGetId(['assigned_user_id' => $cs->id, 'contact_raw' => 'archived', 'archived_at' => now()]);

        $this->actingAs($cs)->patch("/leads/{$active}/archive")->assertForbidden();
        $this->actingAs($cs)->post('/leads/archive', ['lead_ids' => [$active]])->assertForbidden();
        $this->actingAs($cs)->patch("/leads/{$archived}/restore")->assertForbidden();
        $this->actingAs($cs)->post('/leads/restore', ['lead_ids' => [$archived]])->assertForbidden();
        $this->actingAs($cs)->delete("/leads/{$archived}")->assertForbidden();
        $this->actingAs($cs)->delete('/leads', ['lead_ids' => [$archived]])->assertForbidden();
        $this->actingAs($cs)->get('/leads?archived=1')->assertForbidden();
        $this->actingAs($cs)->get("/leads/{$archived}")->assertNotFound();
        $this->actingAs($cs)->get("/leads/{$archived}/history-preview")->assertNotFound();
        $this->assertDatabaseHas('leads', ['id' => $active, 'archived_at' => null]);
        $this->assertDatabaseHas('leads', ['id' => $archived]);
    }

    public function test_admin_can_manage_selected_lead_archive_and_delete(): void
    {
        $admin = $this->user('admin');
        $lead = \DB::table('leads')->insertGetId(['contact_raw' => '081234567890']);
        $this->actingAs($admin)->post('/leads/archive', ['lead_ids' => [$lead]])->assertRedirect();
        $this->assertNotNull(\DB::table('leads')->where('id', $lead)->value('archived_at'));
        $this->actingAs($admin)->post('/leads/restore', ['lead_ids' => [$lead]])->assertRedirect();
        $this->assertDatabaseHas('leads', ['id' => $lead, 'archived_at' => null]);
        $this->actingAs($admin)->post('/leads/archive', ['lead_ids' => [$lead]])->assertRedirect();
        $this->actingAs($admin)->delete('/leads', ['lead_ids' => [$lead]])->assertRedirect();
        $this->assertDatabaseMissing('leads', ['id' => $lead]);
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
        $response = $this->actingAs($cs)->post('/leads', [
            'contact_raw' => '+62 851-7777-7777',
            'milestone' => 'asked',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'contact_key' => 'phone:6285177777777',
            'assigned_user_id' => $cs->id,
            'current_stage' => 'asked',
        ]);
    }

    public function test_legacy_collaborator_can_update_and_cannot_be_removed_through_retired_endpoint(): void
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

        \DB::table('lead_collaborators')->insert(['lead_id' => $leadId, 'user_id' => $collaborator->id, 'added_at' => now()]);
        $this->assertDatabaseHas('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $collaborator->id, 'removed_at' => null]);

        $this->actingAs($collaborator)
            ->patch("/leads/{$leadId}", ['inline' => true, 'keterangan' => 'Follow-up oleh CS pendamping'])
            ->assertRedirect();
        $this->assertDatabaseHas('leads', ['id' => $leadId, 'keterangan' => 'Follow-up oleh CS pendamping']);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'actor_id' => $collaborator->id]);

        $this->actingAs($admin)
            ->patch("/leads/{$leadId}/collaborators", ['user_ids' => []])
            ->assertStatus(410);
        $this->assertDatabaseHas('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $collaborator->id, 'removed_at' => null]);
        $this->actingAs($collaborator)->get("/leads/{$leadId}")->assertInertia(
            fn (Assert $page) => $page
                ->component('Leads/Show')
                ->where('handlingRole', 'collaborator'),
        );
        $this->actingAs($collaborator)->patch("/leads/{$leadId}/archive")->assertForbidden();
        $this->assertNull(\DB::table('leads')->where('id', $leadId)->value('archived_at'));
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

    public function test_admin_cannot_add_collaborators_through_retired_bulk_endpoint(): void
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
            ->assertStatus(410);

        $this->assertDatabaseMissing('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $collaborator->id]);
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

    public function test_cs_can_see_a_lead_waiting_for_its_source_account(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branchId]);
        \DB::table('leads')->insert([
            'name' => 'Lead menunggu akun',
            'source_branch_id' => $branchId,
            'owner_resolution' => 'awaiting_account',
            'imported_cs_name' => 'CS Baru',
            'contact_raw' => '+6285100000025',
            'contact_key' => 'phone:6285100000025',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($cs)->get('/leads')->assertInertia(
            fn (Assert $page) => $page
                ->component('Leads/Index')
                ->has('leads.data', 1)
                ->where('leads.data.0.imported_cs_name', 'CS Baru'),
        );
    }

    public function test_branches_show_only_the_configured_service_account_email(): void
    {
        $admin = $this->user('admin');
        $path = tempnam(sys_get_temp_dir(), 'kgj-credentials-');
        $this->assertNotFalse($path);
        try {
            file_put_contents($path, json_encode(['client_email' => 'sync@example.test', 'private_key' => 'secret-test-key'], JSON_THROW_ON_ERROR));
            config(['leads.google_sheets.credentials_path' => $path]);
            $this->actingAs($admin)->get('/branches')->assertInertia(fn (Assert $page) => $page->where('service_account_email', 'sync@example.test')->missing('private_key'));
            file_put_contents($path, '{invalid');
            $this->actingAs($admin)->get('/branches')->assertInertia(fn (Assert $page) => $page->where('service_account_email', null));
        } finally {
            unlink($path);
        }
    }

    public function test_source_cs_account_status_uses_active_branch_accounts_and_aliases(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $otherBranch = \DB::table('branches')->insertGetId(['name' => 'Solo']);
        $admin = $this->user('admin');
        $cs = $this->user('cs', ['name' => 'Linda', 'branch_id' => $branchId]);
        $this->user('cs', ['name' => 'Tidak Aktif', 'branch_id' => $branchId, 'active' => false]);
        $this->user('cs', ['name' => 'Cabang Lain', 'branch_id' => $otherBranch]);
        $this->user('cs', ['name' => 'Ganda', 'branch_id' => $branchId]);
        $this->user('cs', ['name' => 'Ganda', 'branch_id' => $branchId]);
        \DB::table('cs_source_name_aliases')->insert(['branch_id' => $branchId, 'user_id' => $cs->id, 'source_name' => 'Lindy', 'source_name_key' => 'lindy']);
        $id = \DB::table('leads')->insertGetId(['name' => 'Lead', 'source_branch_id' => $branchId, 'imported_cs_name' => 'Linda', 'owner_resolution' => 'manual', 'created_at' => now(), 'updated_at' => now()]);
        foreach ([' LINDA ' => 'matched', 'Lindy' => 'matched', 'Tidak Aktif' => 'missing', 'Cabang Lain' => 'missing', 'Ganda' => 'ambiguous', 'Baru' => 'missing', '' => null] as $name => $expected) {
            \DB::table('leads')->where('id', $id)->update(['imported_cs_name' => $name]);
            foreach ([$admin, $cs] as $viewer) {
                $this->actingAs($viewer)->get('/leads')->assertInertia(fn (Assert $page) => $page->where('leads.data.0.source_cs_account_status', $expected));
            }
        }
        \DB::table('leads')->where('id', $id)->update(['source_branch_id' => null, 'imported_cs_name' => 'Linda']);
        $this->actingAs($admin)->get('/leads')->assertInertia(fn (Assert $page) => $page->where('leads.data.0.source_cs_account_status', 'branch_missing'));
    }

    public function test_lead_source_account_status_distinguishes_import_history_pending_link_and_archive(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $admin = $this->user('admin');
        $cs = $this->user('cs', ['name' => 'Linda', 'branch_id' => $branchId]);
        $id = \DB::table('leads')->insertGetId(['source_branch_id' => $branchId, 'imported_cs_name' => 'Linda', 'owner_resolution' => 'awaiting_account', 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($admin)->get('/leads')->assertInertia(fn (Assert $page) => $page->where('leads.data.0.source_cs_account_status', 'awaiting_link'));
        $this->assertDatabaseHas('leads', ['id' => $id, 'assigned_user_id' => null, 'owner_resolution' => 'awaiting_account']);
        \DB::table('leads')->where('id', $id)->update(['imported_cs_name' => '', 'owner_resolution' => 'manual', 'assigned_user_id' => $cs->id]);
        $this->actingAs($admin)->get('/leads')->assertInertia(fn (Assert $page) => $page->where('leads.data.0.source_cs_account_status', null));
        \DB::table('lead_activity')->insert(['lead_id' => $id, 'action' => 'Diimpor dari tab harian', 'created_at' => now()]);
        $this->actingAs($admin)->get('/leads')->assertInertia(fn (Assert $page) => $page->where('leads.data.0.source_cs_account_status', 'source_empty'));
        \DB::table('lead_activity')->insert(['lead_id' => $id, 'action' => 'Lead dibuat manual', 'created_at' => now()]);
        $this->actingAs($admin)->get('/leads')->assertInertia(fn (Assert $page) => $page->where('leads.data.0.source_cs_account_status', null));
        \DB::table('leads')->where('id', $id)->update(['imported_cs_name' => 'Tidak Ada', 'archived_at' => now()]);
        $this->actingAs($admin)->get('/leads?archived=1')->assertInertia(fn (Assert $page) => $page->where('leads.data.0.source_cs_account_status', null));
    }

    public function test_retired_assistance_endpoints_preserve_existing_collaborators(): void
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
            ->assertStatus(410);

        $this->assertDatabaseMissing('lead_collaborators', [
            'lead_id' => $leadId,
            'user_id' => $backup->id,
        ]);
        \DB::table('lead_collaborators')->insert(['lead_id' => $leadId, 'user_id' => $backup->id, 'added_at' => now()]);
        $this->actingAs($backup)
            ->delete("/leads/{$leadId}/stop-assisting")
            ->assertStatus(410);

        $this->assertDatabaseHas('lead_collaborators', [
            'lead_id' => $leadId,
            'user_id' => $backup->id,
            'removed_at' => null,
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

    public function test_retired_assistance_endpoint_does_not_add_collaborators_across_branches(): void
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
            ->assertStatus(410);

        $this->assertDatabaseMissing('lead_collaborators', [
            'lead_id' => $leadId,
            'user_id' => $palaganCs->id,
        ]);
    }

    public function test_team_member_can_update_but_not_archive_a_branch_lead(): void
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
            ->patch("/leads/{$leadId}", ['inline' => true, 'keterangan' => 'Diperbarui tim cabang'])
            ->assertRedirect();
        $this->actingAs($teamMember)
            ->patch("/leads/{$leadId}/archive")
            ->assertForbidden();

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'assigned_user_id' => $primary->id, 'keterangan' => 'Diperbarui tim cabang', 'archived_at' => null]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'actor_id' => $teamMember->id, 'action' => 'Lead diperbarui']);
    }

    public function test_team_filter_includes_legacy_collaborators_and_leads_without_an_owner(): void
    {
        $branchId = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $owner = $this->user('cs', ['branch_id' => $branchId]);
        $member = $this->user('cs', ['branch_id' => $branchId]);
        $first = \DB::table('leads')->insertGetId(['assigned_user_id' => $owner->id, 'source_branch_id' => $branchId, 'contact_raw' => '081234567890']);
        \DB::table('leads')->insert(['source_branch_id' => $branchId, 'contact_raw' => '081234567891']);
        \DB::table('lead_collaborators')->insert(['lead_id' => $first, 'user_id' => $member->id, 'added_at' => now()]);
        $this->actingAs($member)->get('/leads?handling=team')->assertInertia(
            fn (Assert $page) => $page->component('Leads/Index')->has('leads.data', 2),
        );
    }

    public function test_cross_branch_legacy_collaboration_does_not_grant_access(): void
    {
        $a = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $b = \DB::table('branches')->insertGetId(['name' => 'Solo']);
        $cs = $this->user('cs', ['branch_id' => $a]);
        $leadId = \DB::table('leads')->insertGetId(['source_branch_id' => $b, 'contact_raw' => '081234567890']);
        $assignedId = \DB::table('leads')->insertGetId(['source_branch_id' => $b, 'assigned_user_id' => $cs->id, 'contact_raw' => '081234567899']);
        $unknownId = \DB::table('leads')->insertGetId(['contact_raw' => '081234567898']);
        \DB::table('lead_collaborators')->insert(['lead_id' => $leadId, 'user_id' => $cs->id, 'added_at' => now()]);
        \DB::table('lead_collaborators')->insert(['lead_id' => $unknownId, 'user_id' => $cs->id, 'added_at' => now()]);
        $this->actingAs($cs)->get('/leads')->assertInertia(fn (Assert $page) => $page->has('leads.data', 0));
        $this->actingAs($cs)->get("/leads/{$leadId}")->assertNotFound();
        $this->actingAs($cs)->patch("/leads/{$leadId}", ['inline' => true, 'keterangan' => 'lintas cabang'])->assertNotFound();
        $this->actingAs($cs)->patch("/leads/{$leadId}/archive")->assertForbidden();
        $this->actingAs($cs)->get("/leads/{$assignedId}")->assertNotFound();
        $this->actingAs($cs)->get("/leads/{$unknownId}")->assertNotFound();
        $this->assertDatabaseHas('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $cs->id, 'removed_at' => null]);
    }

    public function test_old_collaborator_filter_is_normalized_to_team(): void
    {
        $branch = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $cs = $this->user('cs', ['branch_id' => $branch]);
        \DB::table('leads')->insert(['source_branch_id' => $branch, 'contact_raw' => '081234567891']);
        $this->actingAs($cs)->get('/leads?handling=collaborator')->assertInertia(
            fn (Assert $page) => $page->where('filters.handling', 'team')->has('leads.data', 1),
        );
    }

    public function test_cs_filter_matches_source_name_and_alias_not_legacy_collaboration(): void
    {
        $branch = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $other = $this->user('cs', ['branch_id' => $branch, 'name' => 'Nanda']);
        $cs = $this->user('cs', ['branch_id' => $branch, 'name' => 'Dilla']);
        \DB::table('cs_source_name_aliases')->insert(['branch_id' => $branch, 'user_id' => $cs->id, 'source_name' => 'Dil', 'source_name_key' => 'dil']);
        $wrong = \DB::table('leads')->insertGetId(['source_branch_id' => $branch, 'assigned_user_id' => $other->id, 'imported_cs_name' => 'Nanda', 'contact_raw' => '081234567890']);
        \DB::table('lead_collaborators')->insert(['lead_id' => $wrong, 'user_id' => $cs->id, 'added_at' => now()]);
        \DB::table('leads')->insert(['source_branch_id' => $branch, 'assigned_user_id' => $other->id, 'imported_cs_name' => 'Dil', 'contact_raw' => '081234567891']);
        $this->actingAs($cs)->get("/leads?cs_user_id={$cs->id}")->assertInertia(
            fn (Assert $page) => $page->has('leads.data', 1)->where('leads.data.0.imported_cs_name', 'Dil'),
        );
    }

    public function test_branch_transfer_requires_confirmation_to_end_legacy_assignments(): void
    {
        $admin = $this->user('admin');
        $a = \DB::table('branches')->insertGetId(['name' => 'Palagan']);
        $b = \DB::table('branches')->insertGetId(['name' => 'Solo']);
        $cs = $this->user('cs', ['branch_id' => $a, 'name' => 'Dilla']);
        $leadId = \DB::table('leads')->insertGetId(['source_branch_id' => $a, 'contact_raw' => '081234567890']);
        \DB::table('lead_collaborators')->insert(['lead_id' => $leadId, 'user_id' => $cs->id, 'added_at' => now()]);
        $data = ['name' => 'Dilla', 'email' => $cs->email, 'role' => 'cs', 'branch_id' => $b, 'active' => true];
        $this->actingAs($admin)->patch("/accounts/{$cs->id}", $data)->assertSessionHasErrors('release_historical_confirmed');
        $this->assertDatabaseHas('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $cs->id, 'removed_at' => null]);
        $this->actingAs($admin)->patch("/accounts/{$cs->id}", [...$data, 'release_historical_confirmed' => true])->assertRedirect();
        $this->assertNotNull(\DB::table('lead_collaborators')->where('lead_id', $leadId)->where('user_id', $cs->id)->value('removed_at'));
        $this->assertDatabaseHas('users', ['id' => $cs->id, 'branch_id' => $b]);
        $this->assertDatabaseHas('lead_activity', ['lead_id' => $leadId, 'actor_id' => $admin->id, 'action' => 'Hak pendamping historis diakhiri']);
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
            ->assertStatus(410);
        $this->actingAs($admin)
            ->post('/leads/collaborators', ['lead_ids' => [$leadId], 'user_id' => $bandungCs->id])
            ->assertStatus(410);

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'assigned_user_id' => $palaganCs->id]);
        $this->assertDatabaseMissing('lead_collaborators', ['lead_id' => $leadId, 'user_id' => $bandungCs->id]);
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

    private function writeDailyLeadSheet(Writer $writer, string $tab, string $date, string $phone, string $csName = 'Dila'): void
    {
        $writer->getCurrentSheet()->setName($tab);
        $writer->addRow(Row::fromValues([null, 'Monitoring Leads']));
        $writer->addRow(Row::fromValues([null, $date]));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['Whatsapp 1'])));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['No', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting*', 'Waktu Chat', 'Keterangan Sumber*', 'Keterangan Campaign*', 'Definisi Leads'])));
        $writer->addRow(Row::fromValues(array_merge(array_fill(0, 15, ''), ['1', $phone, '0', $csName, 'klik saja', 'nikah', 'halo', '10.38', 'Iklan', 'Bio', 'Cold'])));
    }
}
