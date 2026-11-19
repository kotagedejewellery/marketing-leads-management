<?php

namespace App\Support;

final class LeadOutcomeFactors
{
    /** @var array<string, string> */
    public const CLOSING = [
        'competitive_price' => 'Harga Kompetitif / Sesuai Budget',
        'trusted_quality' => 'Kualitas & Kadar Terpercaya',
        'custom_design' => 'Bisa Custom Sesuai Keinginan',
        'responsive_service' => 'Respon Cepat & Ramah',
        'brand_trust' => 'Kepercayaan / Testimoni / Reputasi Brand',
        'warranty' => 'Garansi & After Sales',
        'promotion' => 'Promo / Diskon Menarik',
        'payment_ease' => 'Kemudahan Pembayaran (DP / Cicilan)',
        'referral' => 'Rekomendasi Teman / Keluarga',
        'offline_experience' => 'Pengalaman di Toko / Offline Meyakinkan',
        'bonus' => 'Bonus Menarik',
        'other' => 'Lainnya',
    ];

    /** @var array<string, string> */
    public const LOSS = [
        'price_expectation' => 'Harga Tidak Sesuai Ekspektasi',
        'production_time' => 'Waktu Pengerjaan Terlalu Lama',
        'design_mismatch' => 'Model / Desain Kurang Sesuai',
        'material_budget' => 'Kadar / Bahan Tidak Sesuai Budget',
        'trust_issue' => 'Kurang Yakin / Trust Issue',
        'slow_response' => 'Respon Lambat',
        'bought_elsewhere' => 'Sudah Beli di Tempat Lain',
        'budget_cancelled' => 'Budget Berubah / Batal Beli',
        'ghosting' => 'Tidak Ada Kabar Lagi (Ghosting)',
        'partner_discussion' => 'Diskusi Pasangan / Orang Tua',
        'buyback' => 'Mau Buyback',
        'survey' => 'Sekedar Survei (Tidak Ada Urgensi Acara)',
        'selecting_model' => 'Masih Pilih Model (Ghosting Setelah Katalog)',
        'nusantara_overbudget' => 'Mau Nusantara Tapi Overbudget',
        'diamond_overbudget' => 'Mau Berlian Tapi Overbudget',
        'wait_weekend' => 'Mau Ke Store (Nunggu Weekend)',
        'inaccessible_store' => 'Mau Ke Store (Tapi Akses Tidak Terjangkau)',
        'other' => 'Lainnya',
    ];

    /** @return array<string, string> */
    public static function forOutcome(string $outcome): array
    {
        return match ($outcome) {
            'closing' => self::CLOSING,
            'loss' => self::LOSS,
            default => [],
        };
    }

    /** @return array{closing: array<string, string>, loss: array<string, string>} */
    public static function all(): array
    {
        return [
            'closing' => self::CLOSING,
            'loss' => self::LOSS,
        ];
    }

    public static function isValid(string $outcome, string $factor): bool
    {
        return array_key_exists($factor, self::forOutcome($outcome));
    }

    public static function label(string $outcome, string $factor): string
    {
        return self::forOutcome($outcome)[$factor] ?? $factor;
    }
}
