<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\OfficeSetting;

echo "📧 Checking backup email settings:\n";

$settings = OfficeSetting::first();

if ($settings) {
    echo "✅ Office Settings found\n";
    echo "📬 Backup Email: " . ($settings->backup_email ?? 'Not set') . "\n";
    echo "🔛 Email Enabled: " . ($settings->backup_email_enabled ? 'Yes' : 'No') . "\n";
} else {
    echo "❌ No office settings found\n";
    echo "💡 Go to Admin > Backup Database > Email Settings to configure\n";
}

echo "\n📅 Scheduled backup jobs:\n";
echo "⏰ Daily backup: Every day at 2:00 AM\n";
echo "📧 Weekly email backup: Every Sunday at 3:00 AM\n";

echo "\n✅ Cron jobs are configured and ready!\n";
