<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use App\Mail\BackupDatabaseMail;
use App\Models\OfficeSetting;

class BackupAndEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup-email {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database dan kirim via email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Memulai backup database untuk email...');

        // Generate backup filename with date
        $filename = 'backup_weekly_' . date('Y-m-d_His');

        // Call backup command
        $exitCode = Artisan::call('db:backup', [
            '--name' => $filename
        ]);

        if ($exitCode !== 0) {
            $this->error('❌ Backup gagal!');
            return Command::FAILURE;
        }

        $backupPath = storage_path('app/backups/' . $filename . '.sql');

        if (!file_exists($backupPath)) {
            $this->error('❌ File backup tidak ditemukan!');
            return Command::FAILURE;
        }

        // Get backup info
        $fileSize = filesize($backupPath);
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);
        $backupDate = date('d F Y, H:i:s');

        $this->info("✅ Backup berhasil: {$fileSizeMB} MB");
        $this->info('📧 Mengirim email...');

        // Get email from option, database settings, or config
        $recipientEmail = $this->option('email');

        if (!$recipientEmail) {
            $settings = OfficeSetting::first();
            if ($settings && $settings->backup_email_enabled && $settings->backup_email) {
                $recipientEmail = $settings->backup_email;
            }
        }

        if (!$recipientEmail) {
            $recipientEmail = config('mail.backup_recipient', env('MAIL_FROM_ADDRESS'));
        }

        if (!$recipientEmail) {
            $this->error('❌ Email tujuan tidak ditemukan!');
            $this->warn('Gunakan: php artisan db:backup-email --email=admin@example.com');
            $this->warn('Atau aktifkan di menu Admin > Backup Database > Email Settings');
            $this->warn('Atau set MAIL_BACKUP_RECIPIENT di .env');
            return Command::FAILURE;
        }

        // Check if email backup is enabled
        $settings = OfficeSetting::first();
        if (!$this->option('email') && $settings && !$settings->backup_email_enabled) {
            $this->warn('⚠️  Email backup tidak diaktifkan di pengaturan');
            $this->info('💡 File backup tetap tersimpan di: ' . $backupPath);
            return Command::SUCCESS;
        }

        try {
            // Send email with backup
            Mail::to($recipientEmail)->send(
                new BackupDatabaseMail($backupPath, $fileSizeMB, $backupDate)
            );

            $this->info("✅ Email berhasil dikirim ke: {$recipientEmail}");

            // Additional info
            if ($fileSize > 25 * 1024 * 1024) {
                $this->warn('⚠️  File terlalu besar (>25MB), tidak dilampirkan di email');
                $this->info('💡 File tersimpan di: ' . $backupPath);
            } else {
                $this->info('📎 File backup dilampirkan di email');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Gagal mengirim email!');
            $this->error('Error: ' . $e->getMessage());
            $this->warn('💡 File backup tetap tersimpan di: ' . $backupPath);

            return Command::FAILURE;
        }
    }
}
