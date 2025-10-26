<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Console\ScheduleRunMiddleware;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Update tracking setiap kali schedule:run dipanggil
// Ini akan dijalankan sebelum semua scheduled tasks
Schedule::call(function () {
    ScheduleRunMiddleware::updateTracking();
})->everyMinute()->name('update-cron-tracking');

// Schedule: Generate absent attendance setiap jam
// Mengecek apakah ada karyawan yang belum absen setelah melewati jam checkout + 30 menit
Schedule::command('attendance:generate-absent')
    ->hourly()
    ->between('08:00', '23:59')
    ->weekdays()
    ->before(function () {
        // Update cache untuk tracking
        Cache::put('cron_last_run', now(), now()->addDays(7));

        // Update sentinel file untuk tracking (better for cPanel)
        $sentinelFile = storage_path('framework/schedule-sentinel');
        touch($sentinelFile);
    })
    ->after(function () {
        // Update timestamp after successful run
        Cache::put('cron_last_run', now(), now()->addDays(7));

        $sentinelFile = storage_path('framework/schedule-sentinel');
        touch($sentinelFile);
    });

// Schedule: Automatic database backup every day at 2 AM
Schedule::command('db:backup')
    ->daily()
    ->at('02:00')
    ->name('auto-backup-daily')
    ->onSuccess(function () {
        // Clean old backups - keep only last 7 days
        $backupPath = storage_path('app/backups');
        if (is_dir($backupPath)) {
            $files = glob($backupPath . '/backup_*.sql');

            // Sort files by modification time (oldest first)
            usort($files, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Keep only last 7 files
            $filesToDelete = array_slice($files, 0, max(0, count($files) - 7));
            foreach ($filesToDelete as $file) {
                @unlink($file);
            }
        }
    })
    ->onFailure(function () {
        // Log backup failure
        Log::error('Automatic database backup failed at ' . now());
    });

// Schedule: Weekly backup via email every Sunday at 3 AM
Schedule::command('db:backup-email')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->name('auto-backup-weekly-email')
    ->onSuccess(function () {
        Log::info('Weekly backup email sent successfully at ' . now());
    })
    ->onFailure(function () {
        Log::error('Weekly backup email failed at ' . now());
    });
