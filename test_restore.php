<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Employee;

echo "=== TEST RESTORE FUNCTIONALITY ===\n\n";

// Show current data
echo "Data sebelum restore:\n";
echo "- Total users: " . User::count() . "\n";
echo "- Total employees: " . Employee::count() . "\n\n";

// Get backup file
$backupFile = 'test_backup.sql';
$backupPath = storage_path('app/backups/' . $backupFile);

if (!file_exists($backupPath)) {
    echo "❌ File backup tidak ditemukan: {$backupPath}\n";
    exit(1);
}

echo "File backup: {$backupFile} (" . round(filesize($backupPath) / 1024, 2) . " KB)\n\n";

try {
    // Read SQL file
    echo "📖 Membaca file SQL...\n";
    $sqlContent = file_get_contents($backupPath);

    if (empty($sqlContent)) {
        echo "❌ File SQL kosong!\n";
        exit(1);
    }

    echo "✅ File SQL berhasil dibaca (" . strlen($sqlContent) . " bytes)\n\n";

    // Get database config
    $dbHost = config('database.connections.mysql.host');
    $dbPort = config('database.connections.mysql.port');
    $dbName = config('database.connections.mysql.database');
    $dbUser = config('database.connections.mysql.username');
    $dbPass = config('database.connections.mysql.password');

    echo "🔌 Koneksi database:\n";
    echo "- Host: {$dbHost}:{$dbPort}\n";
    echo "- Database: {$dbName}\n";
    echo "- User: {$dbUser}\n\n";

    // Create PDO connection
    echo "🔄 Membuat koneksi PDO...\n";
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    echo "✅ Koneksi PDO berhasil\n\n";

    // Disable foreign key checks
    echo "🔓 Menonaktifkan foreign key checks...\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

    // Split and execute SQL statements
    echo "⚡ Mengeksekusi SQL statements...\n";
    $statements = array_filter(
        array_map('trim', explode(';', $sqlContent)),
        function ($statement) {
            return !empty($statement);
        }
    );

    $successCount = 0;
    $errorCount = 0;
    $lastError = null;

    foreach ($statements as $index => $statement) {
        try {
            // Skip comments and empty lines
            if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
                continue;
            }

            $pdo->exec($statement . ';');
            $successCount++;

            if ($successCount % 10 === 0) {
                echo "  ✓ {$successCount} statements executed...\n";
            }
        } catch (PDOException $e) {
            $errorCount++;
            $lastError = $e->getMessage();
            echo "  ⚠ Error on statement " . ($index + 1) . ": " . substr($e->getMessage(), 0, 80) . "...\n";
        }
    }

    // Re-enable foreign key checks
    echo "\n🔒 Mengaktifkan kembali foreign key checks...\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    echo "\n=== HASIL RESTORE ===\n";
    echo "✅ Success: {$successCount} statements\n";
    if ($errorCount > 0) {
        echo "⚠ Errors: {$errorCount} statements\n";
        echo "Last error: {$lastError}\n";
    }

    // Show data after restore
    echo "\nData setelah restore:\n";
    echo "- Total users: " . User::count() . "\n";
    echo "- Total employees: " . Employee::count() . "\n\n";

    if ($successCount > 0) {
        echo "🎉 Restore berhasil!\n";
    } else {
        echo "❌ Restore gagal - tidak ada statement yang berhasil dieksekusi\n";
    }
} catch (PDOException $e) {
    echo "❌ PDO Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
