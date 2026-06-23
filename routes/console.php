<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ежедневная рассылка «Слово дня» в 10:00.
Schedule::command('chinese:daily')->dailyAt('10:00')->withoutOverlapping();
