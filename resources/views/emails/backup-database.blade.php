<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Database Otomatis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            background: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
        }

        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: bold;
            color: #6c757d;
        }

        .value {
            color: #495057;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
            margin-top: 20px;
        }

        .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="icon">💾</div>
        <h1 style="margin: 0;">Backup Database Otomatis</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">{{ config('app.name') }}</p>
    </div>

    <div class="content">
        <p>Halo Admin,</p>

        <p>Backup database telah berhasil dibuat secara otomatis. Berikut informasi detailnya:</p>

        <div class="info-box">
            <div class="info-row">
                <span class="label">📁 Nama File:</span>
                <span class="value">{{ basename($backupPath) }}</span>
            </div>
            <div class="info-row">
                <span class="label">💾 Ukuran File:</span>
                <span class="value">{{ $backupSize }} MB</span>
            </div>
            <div class="info-row">
                <span class="label">📅 Tanggal Backup:</span>
                <span class="value">{{ $backupDate }}</span>
            </div>
            <div class="info-row">
                <span class="label">🗄️ Database:</span>
                <span class="value">{{ config('database.connections.mysql.database') }}</span>
            </div>
            <div class="info-row">
                <span class="label">🖥️ Server:</span>
                <span class="value">{{ config('database.connections.mysql.host') }}</span>
            </div>
        </div>

        @if (file_exists($backupPath) && filesize($backupPath) < 25 * 1024 * 1024)
            <p style="color: #28a745; font-weight: bold;">✅ File backup dilampirkan pada email ini.</p>
        @else
            <div class="warning-box">
                <strong>⚠️ Perhatian:</strong> File backup terlalu besar untuk dilampirkan via email (>25MB).
                Silakan download langsung dari server atau panel admin.
            </div>
        @endif

        <div class="warning-box">
            <strong>🔒 Keamanan:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>Simpan file backup di tempat yang aman</li>
                <li>Jangan bagikan file ini ke pihak yang tidak berwenang</li>
                <li>Verifikasi integritas backup secara berkala</li>
                <li>Pastikan backup dapat di-restore jika diperlukan</li>
            </ul>
        </div>

        <p>Email ini dikirim secara otomatis oleh sistem setiap 7 hari sekali. Jika Anda membutuhkan backup manual,
            silakan akses panel admin.</p>

        <p style="margin-top: 30px;">Terima kasih,<br><strong>{{ config('app.name') }}</strong></p>
    </div>

    <div class="footer">
        <p>Email otomatis - Mohon tidak membalas email ini</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>

</html>
