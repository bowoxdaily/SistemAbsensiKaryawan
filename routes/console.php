<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Cache;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Generate absent attendance setiap jam
// Mengecek apakah ada karyawan yang belum absen setelah melewati jam checkout + 30 menit
Schedule::command('attendance:generate-absent')
    ->hourly()
    ->between('08:00', '23:59')
    ->weekdays()
    ->before(function () {
        Cache::put('cron_last_run', now(), now()->addDays(7));
    })
    ->after(function () {
        Cache::put('cron_last_run', now(), now()->addDays(7));
    });
