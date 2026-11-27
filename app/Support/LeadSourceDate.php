<?php

namespace App\Support;

use DateTimeImmutable;

final class LeadSourceDate
{
    public static function fromDailyTab(string $tab, string $spreadsheetTitle): ?string
    {
        $months = [
            'januari' => 1, 'january' => 1, 'februari' => 2, 'february' => 2,
            'maret' => 3, 'march' => 3, 'april' => 4, 'mei' => 5, 'may' => 5,
            'juni' => 6, 'june' => 6, 'juli' => 7, 'july' => 7,
            'agustus' => 8, 'august' => 8, 'september' => 9, 'oktober' => 10,
            'october' => 10, 'november' => 11, 'desember' => 12, 'december' => 12,
        ];
        $pattern = '/\b('.implode('|', array_keys($months)).')\s+(\d{4})\b/iu';
        $namedCount = preg_match_all($pattern, $spreadsheetTitle, $matches);
        $numericCount = preg_match_all('/(?<![\d-])(0?[1-9]|1[0-2])[-\/](\d{4})(?!\d)/', $spreadsheetTitle, $numericMatches);
        if (! ctype_digit($tab) || $namedCount + $numericCount !== 1) {
            return null;
        }
        $month = $namedCount === 1 ? $months[mb_strtolower($matches[1][0])] : (int) $numericMatches[1][0];
        $year = (int) ($namedCount === 1 ? $matches[2][0] : $numericMatches[2][0]);
        $day = (int) $tab;

        return checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : null;
    }

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
