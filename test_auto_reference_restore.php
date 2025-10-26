<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the restore functionality
echo "Testing auto-reference restore functionality...\n";

// Create BackupController instance
$controller = new \App\Http\Controllers\Admin\BackupController();

// Create mock request with backup file
$request = new Request();
$request->merge(['backup_file' => 'backup_2025-10-26_191631.sql']);

echo "Starting restore process...\n";

try {
    // Call the restore method with filename parameter
    $response = $controller->restore($request, 'backup_2025-10-26_191631.sql');

    echo "Response: " . json_encode($response->getData()) . "\n";

    // Check final counts
    echo "\nAfter restore:\n";
    echo "Employees: " . \App\Models\Employee::count() . "\n";
    echo "Sub-departments: " . DB::table('sub_departments')->count() . "\n";
    echo "Departments: " . DB::table('departments')->count() . "\n";
    echo "Positions: " . DB::table('positions')->count() . "\n";
    echo "Work Schedules: " . DB::table('work_schedules')->count() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nDone.\n";
