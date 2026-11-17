<?php

namespace App\Support;

use DateTimeImmutable;

final class LeadSourceDate
{
    public static function parse(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_ireplace(
            ['Januari', 'Februari', 'Maret', 'Mei', 'Juni', 'Juli', 'Agustus', 'Oktober', 'Desember'],
            ['January', 'February', 'March', 'May', 'June', 'July', 'August', 'October', 'December'],
            $value,
        );

        foreach (['Y-m-d', 'j/n/Y', 'j-n-Y', 'j F Y', 'j M Y'] as $format) {
            $date = DateTimeImmutable::createFromFormat('!'.$format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }
}
