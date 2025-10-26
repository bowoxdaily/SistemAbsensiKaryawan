# 📧 Setup Email untuk Backup Database

## Masalah: Email Tidak Terkirim

Jika Anda menjalankan `php artisan db:backup-email` tetapi email tidak terkirim, kemungkinan besar **mail configuration masih menggunakan mode `log`** (development mode).

## ✅ Solusi Cepat

### Cek Status Mail Configuration

```bash
php artisan mail:check
```

### Opsi 1: Menggunakan Mailtrap (Recommended untuk Testing) ⭐

**Mailtrap** adalah layanan email testing gratis yang sempurna untuk development.

1. **Daftar Mailtrap** (Gratis):

    - Kunjungi: https://mailtrap.io
    - Sign up gratis
    - Buat inbox baru

2. **Copy Credentials dari Mailtrap**:

    - Buka inbox Anda di Mailtrap
    - Klik "Show Credentials"
    - Copy Username dan Password

3. **Update `.env` file**:

    ```env
    MAIL_MAILER=smtp
    MAIL_HOST=sandbox.smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=your-mailtrap-username
    MAIL_PASSWORD=your-mailtrap-password
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS=noreply@absensi.com
    MAIL_FROM_NAME="Sistem Absensi"
    ```

4. **Refresh Configuration**:

    ```bash
    php artisan config:cache
    ```

5. **Test Email**:

    ```bash
    php artisan mail:check --send-test
    # Atau
    php artisan db:backup-email --email=test@example.com
    ```

6. **Cek Email di Mailtrap**:
    - Login ke Mailtrap
    - Buka inbox Anda
    - Email akan muncul di sana (tidak di email sungguhan)

### Opsi 2: Menggunakan Gmail (Production)

**Gmail** bagus untuk production, tapi butuh setup App Password.

1. **Enable 2-Step Verification** di Google Account Anda:

    - https://myaccount.google.com/security
    - Enable "2-Step Verification"

2. **Generate App Password**:

    - Kunjungi: https://myaccount.google.com/apppasswords
    - Pilih "Mail" dan "Windows Computer"
    - Copy 16-digit password yang digenerate

3. **Update `.env` file**:

    ```env
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=youremail@gmail.com
    MAIL_PASSWORD=your-16-digit-app-password
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS=youremail@gmail.com
    MAIL_FROM_NAME="Sistem Absensi"
    ```

4. **Refresh Configuration**:

    ```bash
    php artisan config:cache
    ```

5. **Test Email**:
    ```bash
    php artisan mail:check --send-test
    ```

### Opsi 3: SMTP Server Lainnya

Anda juga bisa menggunakan:

-   **SendGrid**: https://sendgrid.com
-   **Mailgun**: https://mailgun.com
-   **Amazon SES**: https://aws.amazon.com/ses
-   **SMTP Hosting Anda sendiri**

Cukup update `.env` dengan credentials SMTP mereka.

## 🧪 Testing Email

### 1. Cek Konfigurasi:

```bash
php artisan mail:check
```

### 2. Kirim Test Email Sederhana:

```bash
php artisan mail:check --send-test
```

### 3. Test Backup Email:

```bash
php artisan db:backup-email --email=your-email@example.com
```

### 4. Test dari Web Interface:

1. Login sebagai admin
2. Buka menu **Backup Database**
3. Scroll ke **"Backup Email Otomatis"**
4. Isi email Anda
5. Klik **"Kirim Test Email"**

## 🔍 Troubleshooting

### Email tidak terkirim?

1. **Cek mode mail**:

    ```bash
    php artisan tinker --execute="dd(config('mail.default'));"
    ```

    Jika hasilnya `"log"`, berarti email hanya masuk ke log file, tidak benar-benar dikirim.

2. **Cek log file**:

    ```bash
    tail -f storage/logs/laravel.log
    ```

    atau buka: `storage/logs/laravel.log`

3. **Clear cache**:

    ```bash
    php artisan config:cache
    php artisan cache:clear
    ```

4. **Cek credentials**:

    - Pastikan username/password benar
    - Untuk Gmail, pastikan menggunakan App Password (bukan password biasa)
    - Pastikan 2FA aktif di Gmail

5. **Cek firewall/port**:
    - Port 587 (TLS) untuk Gmail
    - Port 2525 untuk Mailtrap
    - Pastikan tidak diblokir firewall

### Gmail: "Less secure app" error?

Gmail tidak lagi support "less secure apps". Anda **HARUS** menggunakan **App Password**:

1. Enable 2-Step Verification
2. Generate App Password
3. Gunakan App Password di `.env`, bukan password biasa

### Mailtrap: Email tidak muncul?

1. Pastikan credentials benar
2. Cek inbox yang benar (bisa punya multiple inbox)
3. Refresh halaman Mailtrap

## 📋 Checklist Setup

-   [ ] Pilih email provider (Mailtrap/Gmail/lainnya)
-   [ ] Update `.env` dengan credentials
-   [ ] Jalankan `php artisan config:cache`
-   [ ] Test dengan `php artisan mail:check --send-test`
-   [ ] Aktifkan email backup di web interface
-   [ ] Test kirim backup via email
-   [ ] Verifikasi email diterima

## 🎯 Rekomendasi

**Development/Testing**: Gunakan **Mailtrap** ⭐

-   Gratis
-   Setup mudah
-   Tidak spam email sungguhan
-   Bisa inspect HTML email

**Production**: Gunakan **Gmail** atau **Dedicated SMTP**

-   Lebih reliable
-   Professional
-   Email sungguhan terkirim

## 📞 Butuh Bantuan?

Jika masih error, cek:

1. `storage/logs/laravel.log` untuk error message detail
2. Pastikan internet connection OK
3. Test credentials di email client lain (Thunderbird/Outlook)

## 🚀 Quick Start (TL;DR)

```bash
# 1. Daftar Mailtrap (gratis): https://mailtrap.io

# 2. Update .env:
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxx
MAIL_PASSWORD=yyy

# 3. Clear cache
php artisan config:cache

# 4. Test
php artisan mail:check --send-test

# 5. Kirim backup
php artisan db:backup-email --email=test@example.com

# 6. Cek Mailtrap inbox
```

Selesai! 🎉
