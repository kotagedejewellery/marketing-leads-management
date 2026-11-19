<?php

namespace Tests\Unit;

use App\Support\DailyLeadTabParser;
use RuntimeException;
use Tests\TestCase;

class DailyLeadTabParserTest extends TestCase
{
    public function test_it_reads_repeated_daily_blocks_without_colliding_their_row_numbers(): void
    {
        $headers = ['No', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Greeting', 'Waktu Chat', 'Keterangan Sumber', 'Keterangan Campaign', 'Definisi Leads'];
        $parser = new DailyLeadTabParser;

        $result = $parser->parse('17', [
            [],
            ['', '17 November 2026'],
            $this->dailyRow(['Whatsapp 1']),
            $this->dailyRow($headers),
            $this->dailyRow(['1', '+62 851-0000-0001 Rini', '0', 'Sisil', 'Tanya-Tanya', 'Cincin nikah', 'Halo Rini', '10.46', 'Organik', 'Other', 'Warm']),
            $this->dailyRow(['Whatsapp 2']),
            $this->dailyRow($headers),
            $this->dailyRow(['1', '+62 851-0000-0002 Dita', '0', 'Sisil', 'Klik saja', '', 'Halo Dita', '10.48', 'Iklan', 'Bio', 'Cold']),
        ]);

        $this->assertSame(['Whatsapp 1 #1', 'Whatsapp 2 #1'], array_column($result['rows'], 'source_row_no'));
        $this->assertSame('Halo Rini', $result['rows'][0]['greeting']);
        $this->assertSame(1, $result['missing_source_fields']);
    }

    public function test_it_rejects_a_daily_block_without_all_source_headers(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('belum lengkap');

        (new DailyLeadTabParser)->parse('17', [
            [],
            ['', '17 November 2026'],
            $this->dailyRow(['Whatsapp 1']),
            $this->dailyRow(['No', 'Nama/Nomor', 'Respond Time', 'Nama CS', 'Keterangan', 'Kebutuhan', 'Waktu Chat', 'Keterangan Sumber', 'Keterangan Campaign', 'Definisi Leads']),
        ]);
    }

    /** @param list<string> $values
     *  @return list<string> */
    private function dailyRow(array $values): array
    {
        return [...array_fill(0, 15, ''), ...$values];
    }
}
