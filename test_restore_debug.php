<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Employee;

echo "=== RESTORE DEBUG TEST ===\n";

$filename = 'backup_2025-10-26_191631.sql';
$backupPath = storage_path('app/backups/' . $filename);

if (!file_exists($backupPath)) {
    echo "ERROR: Backup file not found: $backupPath\n";
    exit(1);
}

echo "Backup file exists: " . filesize($backupPath) . " bytes\n";

// Check current state
echo "Current employees: " . Employee::count() . "\n";
echo "Current sub_departments: " . DB::table('sub_departments')->count() . "\n";

// Test: Manually disable foreign key checks and try to insert one employee record
echo "\n=== TESTING MANUAL INSERT ===\n";

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    // Try to insert one employee record from backup
    $testInsert = "INSERT INTO `employees` VALUES (47,'EMP002','2','Test Employee','L','Test','2003-05-14','Belum Menikah','Islam','Indonesia','WNI',0,'Test','2','32',2,1,NULL,6,'2025-10-28','Tetap','SMK',1,NULL,NULL,'BCA','123131','2','32','32','test','Test','Test','45253','123456','test@test.com','test','123456',999,'active',NULL,NULL,NOW(),NOW())";

    DB::unprepared($testInsert);
    echo "Manual insert successful\n";

    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    echo "Employees after test insert: " . Employee::count() . "\n";

    // Clean up test record
    DB::table('employees')->where('id', 47)->delete();
} catch (Exception $e) {
    echo "Manual insert failed: " . $e->getMessage() . "\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
}

echo "\n=== TEST COMPLETE ===\n";
