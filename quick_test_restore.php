<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;

echo "=== QUICK TEST RESTORE ===\n\n";

echo "Data SEBELUM restore:\n";
echo "- Employees: " . Employee::count() . "\n";
echo "- Departments: " . Department::count() . "\n";
echo "- Positions: " . Position::count() . "\n\n";

// Call restore via controller
$controller = new \App\Http\Controllers\Admin\BackupController();
$request = new \Illuminate\Http\Request();
$response = $controller->restore($request, 'test_backup.sql');

$data = $response->getData(true);

if ($data['success']) {
    echo "✅ " . $data['message'] . "\n";
    if (isset($data['data'])) {
        echo "\nDetail:\n";
        echo "- Success statements: " . $data['data']['success_statements'] . "\n";
        echo "- Insert statements: " . $data['data']['insert_statements'] . "\n";
        echo "- Error statements: " . $data['data']['error_statements'] . "\n";
        echo "- Employees count: " . $data['data']['employees_count'] . "\n";
        echo "- Users count: " . $data['data']['users_count'] . "\n";
    }
} else {
    echo "❌ " . $data['message'] . "\n";
}

echo "\nData SETELAH restore:\n";
echo "- Employees: " . Employee::count() . "\n";
echo "- Departments: " . Department::count() . "\n";
echo "- Positions: " . Position::count() . "\n\n";

// Show sample data
if (Employee::count() > 0) {
    echo "Sample karyawan:\n";
    $employees = Employee::with('department', 'position')->limit(3)->get();
    foreach ($employees as $emp) {
        echo "- " . $emp->full_name . " (" . ($emp->department->name ?? 'N/A') . " - " . ($emp->position->name ?? 'N/A') . ")\n";
    }
}
