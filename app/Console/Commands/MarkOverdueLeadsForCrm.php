<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class MarkOverdueLeadsForCrm extends Command
{
    protected $signature = 'leads:mark-overdue-crm';

    protected $description = 'Mark leads with overdue follow-up and no newer activity for CRM';

    public function handle(): int
    {
        $now = now();
        $ids = $this->overdueLeads()->limit(250)->pluck('l.id')->map(static fn (mixed $id): int => (int) $id)->all();
        $marked = 0;

        DB::transaction(function () use ($ids, $now, &$marked): void {
            foreach ($ids as $id) {
                $updated = $this->overdueLeads()->where('l.id', $id)->update([
                    'crm_taken_over_at' => $now,
                    'updated_at' => $now,
                    'revision' => DB::raw('revision + 1'),
                ]);
                if ($updated === 0) {
                    continue;
                }

                DB::table('lead_activity')->insert([
                    'lead_id' => $id,
                    'action' => 'Lead ditandai CRM otomatis',
                    'detail' => 'Jadwal tindak lanjut terlewati tanpa aktivitas lanjutan.',
                    'created_at' => $now,
                ]);
                $marked++;
            }
        });

        $this->info("{$marked} lead ditandai CRM.");

        return self::SUCCESS;
    }

    private function overdueLeads(): Builder
    {
        return DB::table('leads as l')
            ->whereNull('l.archived_at')
            ->whereNull('l.crm_taken_over_at')
            ->whereNotNull('l.next_follow_up_at')
            ->where('l.next_follow_up_at', '<=', now())
            ->whereNotExists(function (Builder $activity): void {
                $activity->selectRaw('1')
                    ->from('lead_activity as a')
                    ->whereColumn('a.lead_id', 'l.id')
                    ->whereColumn('a.created_at', '>=', 'l.next_follow_up_at');
            });
    }
}
