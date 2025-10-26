<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database MySQL ke file SQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Memulai backup database...');

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Create backup directory if not exists
        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Generate filename
        $filename = $this->option('name')
            ? $this->option('name') . '.sql'
            : 'backup_' . date('Y-m-d_His') . '.sql';

        $backupPath = $backupDir . '/' . $filename;

        // Find mysqldump executable
        $mysqldump = $this->findMysqlDump();

        if (!$mysqldump) {
            $this->error('❌ mysqldump tidak ditemukan!');
            $this->warn('Pastikan MySQL terinstall dan tambahkan ke PATH, atau set MYSQL_BIN di .env');
            $this->warn('Contoh untuk Windows: MYSQL_BIN="C:\\xampp\\mysql\\bin"');
            return 1;
        }

        // Build mysqldump command
        $command = sprintf(
            '%s --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            $mysqldump,
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($backupPath)
        );

        // Execute backup
        $output = null;
        $resultCode = null;
        exec($command, $output, $resultCode);

        if ($resultCode === 0 && file_exists($backupPath)) {
            $fileSize = filesize($backupPath);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            $this->info("✅ Backup berhasil!");
            $this->info("📁 File: {$filename}");
            $this->info("💾 Ukuran: {$fileSizeMB} MB");
            $this->info("📍 Lokasi: {$backupPath}");

            return Command::SUCCESS;
        } else {
            $this->error('❌ Backup gagal!');
            if (!empty($output)) {
                $this->error('Error: ' . implode("\n", $output));
            }

            return Command::FAILURE;
        }
    }

    /**
     * Find mysqldump executable
     */
    private function findMysqlDump()
    {
        // Check if custom MYSQL_BIN is set in .env
        $mysqlBin = env('MYSQL_BIN');
        if ($mysqlBin) {
            $mysqldumpPath = rtrim($mysqlBin, '/\\') . DIRECTORY_SEPARATOR . 'mysqldump';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $mysqldumpPath .= '.exe';
            }
            if (file_exists($mysqldumpPath)) {
                return escapeshellarg($mysqldumpPath);
            }
        }

        // Check common paths for Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $commonPaths = [
                'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
                'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            ];

            foreach ($commonPaths as $path) {
                if (file_exists($path)) {
                    return escapeshellarg($path);
                }
            }
        }

        // Try to find in PATH
        $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where mysqldump' : 'which mysqldump';
        $output = shell_exec($command);

        if ($output && trim($output)) {
            $path = trim(explode("\n", $output)[0]);
            if (file_exists($path)) {
                return escapeshellarg($path);
            }
        }

        return false;
    }
}
