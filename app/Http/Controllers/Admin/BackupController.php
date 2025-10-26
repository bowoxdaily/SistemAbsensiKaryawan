<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeSetting;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\BackupDatabaseMail;

class BackupController extends Controller
{
    /**
     * Display backup management page
     */
    public function index()
    {
        return view('admin.backup.index');
    }

    /**
     * Get list of backups (API)
     */
    public function list()
    {
        $backupDir = storage_path('app/backups');

        if (!file_exists($backupDir)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $files = scandir($backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                $filePath = $backupDir . '/' . $file;
                $backups[] = [
                    'filename' => $file,
                    'size' => filesize($filePath),
                    'size_mb' => round(filesize($filePath) / 1024 / 1024, 2),
                    'created_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                    'path' => $filePath
                ];
            }
        }

        // Sort by created date descending
        usort($backups, function ($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        return response()->json([
            'success' => true,
            'backups' => $backups
        ]);
    }

    /**
     * Create new backup
     */
    public function create(Request $request)
    {
        try {
            $name = $request->input('name', 'backup_' . date('Y-m-d_His'));

            // Remove extension if provided
            $name = str_replace('.sql', '', $name);

            // Execute backup command
            Artisan::call('db:backup', ['--name' => $name]);

            $output = Artisan::output();

            if (strpos($output, '✅') !== false) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup berhasil dibuat'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup gagal dibuat'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function download($filename)
    {
        $backupPath = storage_path('app/backups/' . $filename);

        if (!file_exists($backupPath)) {
            abort(404, 'File backup tidak ditemukan');
        }

        return response()->download($backupPath);
    }

    /**
     * Delete backup file
     */
    public function delete($filename)
    {
        try {
            $backupPath = storage_path('app/backups/' . $filename);

            if (!file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File backup tidak ditemukan'
                ], 404);
            }

            unlink($backupPath);

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse SQL file content into individual statements
     * This method properly handles comments, strings, and multi-line statements
     */
    private function parseSqlFile($sqlContent)
    {
        $statements = [];
        $currentStatement = '';
        $inString = false;
        $stringChar = '';
        $inComment = false;
        $inMultiLineComment = false;

        $lines = explode("\n", $sqlContent);

        foreach ($lines as $line) {
            $line = rtrim($line);

            // Skip empty lines when not building a statement
            if (empty($line) && empty($currentStatement)) {
                continue;
            }

            // Check for single line comments (when not in string)
            if (!$inString && !$inMultiLineComment) {
                // Skip lines starting with -- or #
                if (preg_match('/^\s*(--|#)/', $line)) {
                    continue;
                }

                // Check for start of multi-line comment
                if (strpos($line, '/*') !== false) {
                    $inMultiLineComment = true;
                }
            }

            // Check for end of multi-line comment
            if ($inMultiLineComment) {
                if (strpos($line, '*/') !== false) {
                    $inMultiLineComment = false;
                }
                continue;
            }

            // Process the line character by character
            $len = strlen($line);
            for ($i = 0; $i < $len; $i++) {
                $char = $line[$i];

                // Handle string delimiters
                if (($char === '"' || $char === "'") && ($i === 0 || $line[$i - 1] !== '\\')) {
                    if (!$inString) {
                        $inString = true;
                        $stringChar = $char;
                    } elseif ($char === $stringChar) {
                        $inString = false;
                        $stringChar = '';
                    }
                }

                // Add character to current statement
                $currentStatement .= $char;

                // Check for statement terminator (semicolon not in string)
                if ($char === ';' && !$inString) {
                    $stmt = trim($currentStatement);
                    if (!empty($stmt)) {
                        $statements[] = $stmt;
                    }
                    $currentStatement = '';
                }
            }

            // Add newline if we're building a multi-line statement
            if (!empty($currentStatement)) {
                $currentStatement .= "\n";
            }
        }

        // Add any remaining statement
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }

        return $statements;
    }

    /**
     * Restore backup using mysql command line tool (more reliable than PHP parsing)
     */
    public function restore(Request $request, $filename)
    {
        try {
            $backupPath = storage_path('app/backups/' . $filename);

            if (!file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File backup tidak ditemukan'
                ], 404);
            }

            Log::info('Starting restore process', [
                'filename' => $filename,
                'file_size' => filesize($backupPath),
                'file_size_mb' => round(filesize($backupPath) / 1024 / 1024, 2)
            ]);

            // Get database configuration
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            // Find mysql executable
            $mysql = $this->findMysql();

            if (!$mysql) {
                Log::warning('mysql executable not found, using fallback method');

                // Fallback to DB::unprepared() method
                return $this->restoreWithDbUnprepared($backupPath, $filename);
            }

            Log::info('Found mysql executable', ['path' => $mysql]);

            // Build mysql restore command for Windows
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows: Create temp file with SET FOREIGN_KEY_CHECKS=0 + backup content + SET FOREIGN_KEY_CHECKS=1
                $tempFile = storage_path('app/temp_restore_' . uniqid() . '.sql');

                // Create temporary SQL file with foreign key checks disabled
                $sqlContent = "SET FOREIGN_KEY_CHECKS=0;\n" .
                    file_get_contents($backupPath) .
                    "\nSET FOREIGN_KEY_CHECKS=1;\n";
                file_put_contents($tempFile, $sqlContent);

                $command = sprintf(
                    '%s --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
                    escapeshellarg($mysql),
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbName),
                    escapeshellarg($tempFile)
                );
            } else {
                // Linux/Mac: Standard redirect with foreign key checks disabled
                $command = sprintf(
                    '(echo "SET FOREIGN_KEY_CHECKS=0;"; cat %s; echo "SET FOREIGN_KEY_CHECKS=1;") | %s --host=%s --port=%s --user=%s --password=%s %s 2>&1',
                    escapeshellarg($backupPath),
                    escapeshellarg($mysql),
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbName)
                );
            }

            Log::info('Executing mysql restore command');

            // Execute restore
            $output = [];
            $resultCode = null;
            exec($command, $output, $resultCode);

            // Clean up temp file if created (Windows)
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            Log::info('Restore command executed', [
                'result_code' => $resultCode,
                'output_lines' => count($output),
                'output_preview' => !empty($output) ? substr(implode(' ', $output), 0, 200) : 'No output'
            ]);

            if ($resultCode !== 0) {
                $errorMsg = !empty($output) ? implode("\n", $output) : 'Unknown error';
                Log::error('Restore command failed', [
                    'result_code' => $resultCode,
                    'error' => $errorMsg
                ]);

                // If mysql command fails, fallback to DB::unprepared
                Log::warning('Falling back to DB::unprepared method', [
                    'mysql_error' => $errorMsg
                ]);
                return $this->restoreWithDbUnprepared($backupPath, $filename);
            }

            // Clear Laravel's cache to ensure fresh data
            Artisan::call('cache:clear');
            Artisan::call('config:clear');

            // Force reconnect database connection
            DB::purge('mysql');
            DB::reconnect('mysql');

            // Create missing reference data if needed
            $this->createMissingReferenceData();

            // Verify data was restored
            $employeeCount = Employee::count();
            $userCount = User::count();

            Log::info('Restore completed successfully', [
                'filename' => $filename,
                'employee_count' => $employeeCount,
                'user_count' => $userCount
            ]);

            $restored = $employeeCount > 0 || $userCount > 0;

            return response()->json([
                'success' => true,
                'restored' => $restored,
                'message' => $restored
                    ? "Restore berhasil! Database telah dikembalikan. {$employeeCount} karyawan tersedia."
                    : "Restore berhasil tetapi tidak ada data yang ditemukan di backup.",
                'data' => [
                    'employees_count' => $employeeCount,
                    'users_count' => $userCount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Restore exception', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fallback restore method using DB::unprepared()
     */
    private function restoreWithDbUnprepared($backupPath, $filename)
    {
        try {
            Log::info('Using fallback restore method (DB::unprepared)');

            // Read SQL file
            $sqlContent = file_get_contents($backupPath);

            if (empty($sqlContent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File backup kosong atau tidak dapat dibaca'
                ], 400);
            }

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement('SET UNIQUE_CHECKS=0');

            // Truncate all tables except migrations
            $tables = DB::select('SHOW TABLES');
            $dbName = config('database.connections.mysql.database');
            $tableKey = "Tables_in_{$dbName}";

            Log::info('Truncating tables before restore');

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                // Skip migrations table
                if ($tableName === 'migrations') {
                    continue;
                }

                try {
                    DB::statement("TRUNCATE TABLE `{$tableName}`");
                    Log::info("Truncated table: {$tableName}");
                } catch (\Exception $e) {
                    try {
                        DB::statement("DELETE FROM `{$tableName}`");
                        Log::info("Deleted data from table: {$tableName}");
                    } catch (\Exception $e2) {
                        Log::warning("Could not clear table {$tableName}: " . $e2->getMessage());
                    }
                }
            }

            // Execute the entire SQL content at once (most reliable for mysqldump format)
            // Remove DELIMITER statements and stored procedure definitions if any
            $sqlContent = preg_replace('/DELIMITER\s+\S+/i', '', $sqlContent);

            Log::info('Executing SQL content directly');

            try {
                // Try executing the whole SQL content at once
                DB::unprepared($sqlContent);
                Log::info('SQL content executed successfully');
                $successCount = 1;
                $errorCount = 0;
            } catch (\Exception $e) {
                Log::warning('Full SQL execution failed, trying statement-by-statement', [
                    'error' => $e->getMessage()
                ]);

                // Fallback: Split and execute statement by statement
                $statements = $this->parseSqlFile($sqlContent);

                Log::info('Executing SQL statements one by one', ['count' => count($statements)]);

                $successCount = 0;
                $errorCount = 0;

                foreach ($statements as $index => $statement) {
                    $statement = trim($statement);

                    if (empty($statement)) {
                        continue;
                    }

                    try {
                        DB::unprepared($statement);
                        $successCount++;
                    } catch (\Exception $e2) {
                        $errorCount++;

                        // Only log significant errors (skip duplicate key errors for migrations)
                        if (stripos($e2->getMessage(), 'migrations') === false) {
                            Log::warning("SQL Error at statement #{$index}", [
                                'error' => $e2->getMessage(),
                                'preview' => substr($statement, 0, 100)
                            ]);
                        }
                    }
                }
            }

            // Create missing reference data before re-enabling foreign key checks
            $this->createMissingReferenceData();

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::statement('SET UNIQUE_CHECKS=1');

            Log::info('SQL execution completed', [
                'success' => $successCount,
                'errors' => $errorCount
            ]);

            // Clear cache and verify
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            DB::purge('mysql');
            DB::reconnect('mysql');

            $employeeCount = Employee::count();
            $userCount = User::count();

            Log::info('Restore verification', [
                'employee_count' => $employeeCount,
                'user_count' => $userCount
            ]);

            $restored = $employeeCount > 0 || $userCount > 0;

            return response()->json([
                'success' => true,
                'restored' => $restored,
                'message' => $restored
                    ? "Restore berhasil! Database telah dikembalikan. {$employeeCount} karyawan tersedia."
                    : "Proses selesai tetapi tidak ada data yang ditemukan.",
                'data' => [
                    'employees_count' => $employeeCount,
                    'users_count' => $userCount,
                    'success_statements' => $successCount,
                    'error_statements' => $errorCount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Fallback restore failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Restore gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create missing reference data that employees might need
     */
    private function createMissingReferenceData()
    {
        Log::info('Creating missing reference data');

        try {
            // Check and create default sub_departments if needed
            $subDeptCount = DB::table('sub_departments')->count();
            $employeesNeedingSubDept = DB::table('employees')
                ->whereNotNull('sub_department_id')
                ->distinct()
                ->pluck('sub_department_id')
                ->toArray();

            if ($subDeptCount === 0 && !empty($employeesNeedingSubDept)) {
                Log::info('Creating missing sub_departments', [
                    'needed_ids' => $employeesNeedingSubDept
                ]);

                // Get departments for reference
                $departments = DB::table('departments')->get();

                foreach ($employeesNeedingSubDept as $subDeptId) {
                    if ($subDeptId && $subDeptId > 0) {
                        // Use first department or create default
                        $deptId = $departments->first()->id ?? 1;

                        DB::table('sub_departments')->insert([
                            'id' => $subDeptId,
                            'department_id' => $deptId,
                            'name' => 'Sub Department ' . $subDeptId,
                            'description' => 'Auto-created during restore',
                            'is_active' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        Log::info("Created sub_department with ID: {$subDeptId}");
                    }
                }
            }

            // Check and create default departments if needed
            $deptCount = DB::table('departments')->count();
            $employeesNeedingDept = DB::table('employees')
                ->whereNotNull('department_id')
                ->distinct()
                ->pluck('department_id')
                ->toArray();

            if ($deptCount === 0 && !empty($employeesNeedingDept)) {
                Log::info('Creating missing departments', [
                    'needed_ids' => $employeesNeedingDept
                ]);

                foreach ($employeesNeedingDept as $deptId) {
                    if ($deptId && $deptId > 0) {
                        DB::table('departments')->insert([
                            'id' => $deptId,
                            'name' => 'Department ' . $deptId,
                            'description' => 'Auto-created during restore',
                            'is_active' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        Log::info("Created department with ID: {$deptId}");
                    }
                }
            }

            // Check and create default positions if needed
            $posCount = DB::table('positions')->count();
            $employeesNeedingPos = DB::table('employees')
                ->whereNotNull('position_id')
                ->distinct()
                ->pluck('position_id')
                ->toArray();

            if ($posCount === 0 && !empty($employeesNeedingPos)) {
                Log::info('Creating missing positions', [
                    'needed_ids' => $employeesNeedingPos
                ]);

                foreach ($employeesNeedingPos as $posId) {
                    if ($posId && $posId > 0) {
                        DB::table('positions')->insert([
                            'id' => $posId,
                            'name' => 'Position ' . $posId,
                            'basic_salary' => 0,
                            'description' => 'Auto-created during restore',
                            'is_active' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        Log::info("Created position with ID: {$posId}");
                    }
                }
            }

            // Check and create default work_schedules if needed
            $scheduleCount = DB::table('work_schedules')->count();
            $employeesNeedingSchedule = DB::table('employees')
                ->whereNotNull('work_schedule_id')
                ->distinct()
                ->pluck('work_schedule_id')
                ->toArray();

            if ($scheduleCount === 0 && !empty($employeesNeedingSchedule)) {
                Log::info('Creating missing work_schedules', [
                    'needed_ids' => $employeesNeedingSchedule
                ]);

                foreach ($employeesNeedingSchedule as $scheduleId) {
                    if ($scheduleId && $scheduleId > 0) {
                        DB::table('work_schedules')->insert([
                            'id' => $scheduleId,
                            'name' => 'Schedule ' . $scheduleId,
                            'start_time' => '08:00:00',
                            'end_time' => '17:00:00',
                            'break_start' => '12:00:00',
                            'break_end' => '13:00:00',
                            'is_active' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        Log::info("Created work_schedule with ID: {$scheduleId}");
                    }
                }
            }

            Log::info('Finished creating missing reference data');
        } catch (\Exception $e) {
            Log::error('Error creating missing reference data', [
                'error' => $e->getMessage()
            ]);
        }
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
                Log::info('Found mysql via MYSQL_BIN', ['path' => $mysqlPath]);
                return $mysqlPath; // Don't escape yet, we'll do it in the command
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
                    Log::info('Found mysql at common path', ['path' => $path]);
                    return $path;
                }
            }

            // Try to find Laragon mysql dynamically
            if (is_dir('C:\\laragon\\bin\\mysql')) {
                $mysqlDirs = glob('C:\\laragon\\bin\\mysql\\mysql-*');
                foreach ($mysqlDirs as $dir) {
                    $mysqlPath = $dir . '\\bin\\mysql.exe';
                    if (file_exists($mysqlPath)) {
                        Log::info('Found mysql in Laragon', ['path' => $mysqlPath]);
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
                Log::info('Found mysql in PATH', ['path' => $path]);
                return $path;
            }
        }

        Log::warning('mysql executable not found');
        return false;
    }

    /**
     * Upload backup file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:102400', // 100MB max
        ], [
            'backup_file.required' => 'File backup wajib dipilih',
            'backup_file.max' => 'Ukuran file maksimal 100MB',
        ]);

        try {
            $file = $request->file('backup_file');
            $filename = $file->getClientOriginalName();

            // Check file extension manually
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension !== 'sql') {
                return response()->json([
                    'success' => false,
                    'message' => 'File harus berformat .sql'
                ], 422);
            }

            // Save to backups directory
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $file->move($backupDir, $filename);

            return response()->json([
                'success' => true,
                'message' => 'File backup berhasil diupload'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get email settings
     */
    public function getEmailSettings()
    {
        $settings = OfficeSetting::first();

        // If no settings exist, return empty values
        if (!$settings) {
            return response()->json([
                'success' => true,
                'data' => [
                    'backup_email' => '',
                    'backup_email_enabled' => false,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'backup_email' => $settings->backup_email ?? '',
                'backup_email_enabled' => $settings->backup_email_enabled ?? false,
            ]
        ]);
    }

    /**
     * Update email settings
     */
    public function updateEmailSettings(Request $request)
    {
        $request->validate([
            'backup_email' => 'required|email',
            'backup_email_enabled' => 'nullable',
        ], [
            'backup_email.required' => 'Email harus diisi',
            'backup_email.email' => 'Format email tidak valid',
        ]);

        try {
            // Get or create office settings (in case table is empty)
            $settings = OfficeSetting::firstOrCreate(
                ['id' => 1],
                [
                    'office_name' => 'Kantor Pusat',
                    'latitude' => -6.200000,
                    'longitude' => 106.816666,
                    'radius_meters' => 100,
                    'enforce_location' => true,
                    'address' => 'Jakarta, Indonesia'
                ]
            );

            $settings->backup_email = $request->backup_email;

            // Convert string/int to boolean properly
            $enabled = $request->backup_email_enabled;
            if (is_string($enabled)) {
                $enabled = in_array(strtolower($enabled), ['true', '1', 'on', 'yes']);
            } else {
                $enabled = (bool) $enabled;
            }

            $settings->backup_email_enabled = $enabled;
            $settings->save();

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan email backup berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test email
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // Create a quick backup for test
            $filename = 'test_email_backup_' . date('YmdHis');
            Artisan::call('db:backup', ['--name' => $filename]);

            $backupPath = storage_path('app/backups/' . $filename . '.sql');

            if (!file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat backup untuk test'
                ], 500);
            }

            $fileSize = filesize($backupPath);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);
            $backupDate = date('d F Y, H:i:s');

            // Send test email
            Mail::to($request->email)->send(
                new BackupDatabaseMail($backupPath, $fileSizeMB, $backupDate)
            );

            // Delete test backup after sending
            @unlink($backupPath);

            return response()->json([
                'success' => true,
                'message' => 'Email test berhasil dikirim ke ' . $request->email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
    }
}
