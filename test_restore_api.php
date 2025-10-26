<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Employee;

echo "=== TEST RESTORE VIA API ===\n\n";

// Show current data
echo "Data SEBELUM restore:\n";
echo "- Users: " . User::count() . "\n";
echo "- Employees: " . Employee::count() . "\n\n";

// Get backup filename
$backupFile = 'test_backup.sql';

// Simulate API call to restore endpoint
$controller = new \App\Http\Controllers\Admin\BackupController();
$request = new \Illuminate\Http\Request();

echo "📦 Melakukan restore dari: {$backupFile}\n\n";

try {
    $response = $controller->restore($request, $backupFile);
    $data = $response->getData(true);

    if ($data['success']) {
        echo "✅ " . $data['message'] . "\n\n";
    } else {
        echo "❌ " . $data['message'] . "\n\n";
    }

    // Show data after restore
    echo "Data SETELAH restore:\n";
    echo "- Users: " . User::count() . "\n";
    echo "- Employees: " . Employee::count() . "\n\n";

    if (Employee::count() > 0) {
        echo "🎉 RESTORE BERHASIL! Data sudah masuk ke database!\n";

        // Show sample employee
        $firstEmployee = Employee::first();
        echo "\nContoh data karyawan:\n";
        echo "- NIP: " . $firstEmployee->employee_id . "\n";
        echo "- Nama: " . $firstEmployee->full_name . "\n";
        echo "- Department: " . ($firstEmployee->department->name ?? 'N/A') . "\n";
    } else {
        echo "⚠ Data tidak masuk ke database\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
