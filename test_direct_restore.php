<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\BackupController;
use Illuminate\Http\Request;

echo "=== DIRECT RESTORE TEST ===\n";

$filename = 'backup_2025-10-26_191631.sql';
$backupPath = storage_path('app/backups/' . $filename);

if (!file_exists($backupPath)) {
    echo "ERROR: Backup file not found\n";
    exit(1);
}

echo "Testing restore with file: $filename\n";
echo "File size: " . filesize($backupPath) . " bytes\n";

// Create controller instance
$controller = new BackupController();

// Create mock request
$request = new Request();

// Call restore method
try {
    $response = $controller->restore($request, $filename);
    $result = $response->getData(true);

    echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
