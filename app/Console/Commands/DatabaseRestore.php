<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Models\Employee;
use App\Models\User;

class DatabaseRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore {file : Nama file backup (dengan atau tanpa ekstensi .sql)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database MySQL dari file SQL backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('file');
        
        // Add .sql extension if not present
        if (!str_ends_with($filename, '.sql')) {
            $filename .= '.sql';
        }

        $backupPath = storage_path('app/backups/' . $filename);

        if (!file_exists($backupPath)) {
            $this->error('❌ File backup tidak ditemukan: ' . $backupPath);
            return Command::FAILURE;
        }

        $this->info('🔄 Memulai restore database...');
        $this->info('📁 File: ' . $filename);
        $this->info('💾 Ukuran: ' . round(filesize($backupPath) / 1024 / 1024, 2) . ' MB');
        $this->newLine();

        if (!$this->confirm('⚠️  PERINGATAN: Restore akan menimpa semua data yang ada. Lanjutkan?', false)) {
            $this->warn('Restore dibatalkan.');
            return Command::FAILURE;
        }

        try {
            // Get database configuration
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            // Find mysql executable
            $mysql = $this->findMysql();

            if ($mysql) {
                $this->info('📍 MySQL executable: ' . $mysql);
                $this->newLine();
                
                return $this->restoreWithMysql($mysql, $backupPath, $dbHost, $dbPort, $dbName, $dbUser, $dbPass);
            } else {
                $this->warn('⚠️  mysql executable tidak ditemukan, menggunakan metode alternatif...');
                $this->newLine();
                
                return $this->restoreWithDbUnprepared($backupPath);
            }

        } catch (\Exception $e) {
            $this->error('❌ Restore gagal: ' . $e->getMessage());
            Log::error('Restore command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Restore using mysql command line tool
     */
    private function restoreWithMysql($mysql, $backupPath, $dbHost, $dbPort, $dbName, $dbUser, $dbPass)
    {
        $this->info('🔄 Mengeksekusi restore dengan mysql command...');

        // Build command
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = sprintf(
                'type %s | %s --host=%s --port=%s --user=%s --password=%s %s 2>&1',
                escapeshellarg($backupPath),
                escapeshellarg($mysql),
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName)
            );
        } else {
            $command = sprintf(
                '%s --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
                escapeshellarg($mysql),
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($backupPath)
            );
        }

        $output = [];
        $resultCode = null;
        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            $errorMsg = !empty($output) ? implode("\n", $output) : 'Unknown error';
            $this->error('❌ Restore gagal!');
            $this->error('Error: ' . $errorMsg);
            return Command::FAILURE;
        }

        $this->verifyRestore();
        
        return Command::SUCCESS;
    }

    /**
     * Restore using DB::unprepared
     */
    private function restoreWithDbUnprepared($backupPath)
    {
        $this->info('🔄 Membaca file backup...');
        
        $sqlContent = file_get_contents($backupPath);

        if (empty($sqlContent)) {
            $this->error('❌ File backup kosong atau tidak dapat dibaca');
            return Command::FAILURE;
        }

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET UNIQUE_CHECKS=0');

        // Truncate all tables except migrations
        $this->info('🗑️  Menghapus data lama...');
        $this->truncateTables();

        // Execute SQL content
        $this->info('📝 Mengeksekusi SQL statements...');
        
        try {
            DB::unprepared($sqlContent);
            $this->info('✅ SQL berhasil dieksekusi');
        } catch (\Exception $e) {
            $this->warn('⚠️  Full SQL execution failed, trying statement-by-statement...');
            $this->executeStatementsOneByOne($sqlContent);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::statement('SET UNIQUE_CHECKS=1');

        $this->verifyRestore();
        
        return Command::SUCCESS;
    }

    /**
     * Truncate all tables except migrations
     */
    private function truncateTables()
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $tableKey = "Tables_in_{$dbName}";
        
        $bar = $this->output->createProgressBar(count($tables));
        $bar->start();
        
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            if ($tableName === 'migrations') {
                $bar->advance();
                continue;
            }
            
            try {
                DB::statement("TRUNCATE TABLE `{$tableName}`");
            } catch (\Exception $e) {
                try {
                    DB::statement("DELETE FROM `{$tableName}`");
                } catch (\Exception $e2) {
                    // Ignore
                }
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
    }

    /**
     * Execute SQL statements one by one
     */
    private function executeStatementsOneByOne($sqlContent)
    {
        // Simple split by semicolon (better parsing can be added if needed)
        $statements = array_filter(
            array_map('trim', explode(';', $sqlContent)),
            function($stmt) {
                return !empty($stmt) && 
                       !preg_match('/^(--|#|\/\*)/', $stmt);
            }
        );

        $bar = $this->output->createProgressBar(count($statements));
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($statements as $statement) {
            try {
                DB::unprepared($statement . ';');
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                // Silently ignore errors
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Berhasil: {$successCount}, Gagal: {$errorCount}");
    }

    /**
     * Verify restore and show results
     */
    private function verifyRestore()
    {
        $this->newLine();
        $this->info('🔍 Memverifikasi restore...');

        // Clear cache
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        DB::purge('mysql');
        DB::reconnect('mysql');

        // Count records
        $employeeCount = Employee::count();
        $userCount = User::count();

        $this->newLine();
        $this->info('✅ Restore berhasil!');
        $this->table(
            ['Table', 'Records'],
            [
                ['Employees', $employeeCount],
                ['Users', $userCount],
            ]
        );
    }

    /**
     * Find mysql executable
     */
    private function findMysql()
    {
        // Check if custom MYSQL_BIN is set in .env
        $mysqlBin = env('MYSQL_BIN');
        if ($mysqlBin) {
            $mysqlPath = rtrim($mysqlBin, '/\\') . DIRECTORY_SEPARATOR . 'mysql';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $mysqlPath .= '.exe';
            }
            if (file_exists($mysqlPath)) {
                return $mysqlPath;
            }
        }

        // Check common paths for Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $commonPaths = [
                'C:\\xampp\\mysql\\bin\\mysql.exe',
                'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysql.exe',
                'C:\\laragon\\bin\\mysql\\mysql-8.0.31-winx64\\bin\\mysql.exe',
                'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysql.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe',
            ];

            foreach ($commonPaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
            
            // Try to find Laragon mysql dynamically
            if (is_dir('C:\\laragon\\bin\\mysql')) {
                $mysqlDirs = glob('C:\\laragon\\bin\\mysql\\mysql-*');
                foreach ($mysqlDirs as $dir) {
                    $mysqlPath = $dir . '\\bin\\mysql.exe';
                    if (file_exists($mysqlPath)) {
                        return $mysqlPath;
                    }
                }
            }
        }

        // Try to find in PATH
        $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where mysql 2>nul' : 'which mysql 2>/dev/null';
        $output = shell_exec($command);

        if ($output && trim($output)) {
            $path = trim(explode("\n", $output)[0]);
            if (file_exists($path)) {
                return $path;
            }
        }

        return false;
    }
}
