<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('leads:sync-google-sheet')
    ->everyTwoMinutes()
    ->between('08:00', '21:00')
    ->withoutOverlapping();
