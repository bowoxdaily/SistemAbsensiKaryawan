# Panduan Pengaturan Cron Job - Sistem Absensi Karyawan

## 📋 Daftar Isi

1. [Pengenalan](#pengenalan)
2. [Fitur](#fitur)
3. [Setup di Berbagai Platform](#setup-di-berbagai-platform)
4. [Cara Menggunakan](#cara-menggunakan)
5. [Troubleshooting](#troubleshooting)

---

## 🎯 Pengenalan

Halaman **Cron Job Settings** adalah fitur yang memudahkan administrator untuk mengatur dan mengelola scheduled tasks (tugas terjadwal) pada aplikasi Sistem Absensi Karyawan.

### Apa itu Cron Job?

Cron Job adalah tugas otomatis yang berjalan secara terjadwal di server. Aplikasi ini membutuhkan Cron Job untuk menjalankan tugas-tugas seperti:

-   Generate absensi alpha otomatis setiap hari pukul 23:59
-   Pembersihan cache
-   Backup database (future)
-   Kirim notifikasi email (future)

---

## ✨ Fitur

### 1. **Auto-Detect Environment**

-   Mendeteksi OS server (Windows/Linux)
-   Menampilkan PHP path otomatis
-   Menampilkan base path aplikasi
-   Menampilkan versi PHP dan Laravel

### 2. **Multiple Command Format**

Sistem menyediakan command untuk berbagai platform:

-   **Linux/Unix** (cPanel, Plesk, VPS)
-   **Windows** (Task Scheduler)
-   **Direct Command** (tanpa cd)

### 3. **One-Click Copy**

-   Tombol copy untuk setiap command
-   Notifikasi success saat berhasil copy
-   Tidak perlu copy manual yang rawan typo

### 4. **Schedule Management**

-   Lihat daftar scheduled tasks
-   Test command secara manual
-   Monitor status cron job

### 5. **Status Monitoring**

-   Last Run Time
-   Next Run Time
-   Cron Status (Active/Inactive)
-   Auto-refresh status

### 6. **Panduan Setup Lengkap**

Tutorial step-by-step untuk:

-   cPanel
-   Plesk
-   VPS/SSH
-   Windows Server

---

## 🚀 Setup di Berbagai Platform

### A. Setup di cPanel

1. Login ke **cPanel** hosting Anda
2. Cari menu **"Cron Jobs"** di bagian Advanced
3. Pilih interval **"Common Settings: Once Per Minute (\*\***)"\*\*
4. Di halaman Cron Job Settings, klik tombol **Copy** pada command Linux
5. Paste ke field **"Command"** di cPanel
6. Klik **"Add New Cron Job"**

**Contoh Command:**

```bash
* * * * * cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Tips:**

-   Pastikan path PHP sudah benar (biasanya `/usr/bin/php` atau `/usr/local/bin/php`)
-   Jika tidak yakin, hubungi hosting provider
-   Beberapa hosting menggunakan `/opt/cpanel/ea-php82/root/usr/bin/php`

---

### B. Setup di Plesk

1. Login ke **Plesk Panel**
2. Pilih **domain/website** Anda
3. Klik **"Scheduled Tasks"** atau **"Cron Jobs"**
4. Klik **"Add Task"**
5. Task type: **"Run a command"**
6. Schedule: `*/1 * * * *` (every minute)
7. Copy command dari halaman Cron Job Settings
8. Paste ke field command
9. Klik **"OK"**

---

### C. Setup di VPS via SSH

1. SSH ke server VPS:

```bash
ssh user@your-server-ip
```

2. Edit crontab:

```bash
crontab -e
```

3. Tekan `i` untuk masuk mode insert

4. Paste command dari halaman Cron Job Settings

5. Tekan `Esc` kemudian ketik `:wq` dan Enter

6. Verifikasi dengan:

```bash
crontab -l
```

**Untuk melihat log:**

```bash
* * * * * cd /var/www/html && /usr/bin/php artisan schedule:run >> /var/www/html/storage/logs/cron.log 2>&1
```

Lihat log dengan:

```bash
tail -f /var/www/html/storage/logs/cron.log
```

---

### D. Setup di Windows (Development/Server)

#### Metode 1: Task Scheduler (Recommended untuk Production)

1. Buka **Task Scheduler** (tekan `Win+R`, ketik `taskschd.msc`)
2. Klik **"Create Basic Task"**
3. Name: `Laravel Scheduler`
4. Description: `Run Laravel scheduled tasks`
5. Trigger: **"Daily"**
6. Start time: `00:00:00`
7. Centang **"Repeat task every"**: `1 minute`
8. Duration: `Indefinitely`
9. Action: **"Start a program"**
10. Program/script: `C:\php\php.exe` (sesuaikan path PHP Anda)
11. Add arguments: `artisan schedule:run`
12. Start in: `C:\xampp\htdocs\SistemAbsensiKaryawan` (path aplikasi Anda)
13. Finish

#### Metode 2: PowerShell Script (Alternative)

Buat file `run-scheduler.ps1`:

```powershell
cd C:\xampp\htdocs\SistemAbsensiKaryawan
php artisan schedule:run
```

Jadwalkan dengan Task Scheduler untuk menjalankan script ini setiap menit.

#### Metode 3: Development Only

```bash
php artisan schedule:work
```

**Catatan:** Hanya untuk testing, jangan digunakan di production!

---

## 📖 Cara Menggunakan

### 1. Akses Halaman Cron Job Settings

Buka menu: **Pengaturan > Cron Job**

URL: `https://your-domain.com/admin/settings/cronjob`

---

### 2. Copy Command

1. Pilih tab platform hosting Anda (Linux/Windows)
2. Klik tombol **"Copy"** pada command yang sesuai
3. Command akan otomatis ter-copy ke clipboard
4. Muncul notifikasi "Command berhasil di-copy"

---

### 3. Monitor Status

#### Check Status

Klik tombol **"Check Status"** untuk melihat:

-   ✅ **Last Run**: Kapan cron terakhir berjalan
-   📅 **Next Run**: Kapan cron akan berjalan lagi
-   🔴/🟢 **Status**: Active/Inactive

#### Indikator Status:

-   🟢 **Active**: Last run dalam 2 menit terakhir
-   🔴 **Inactive**: Last run lebih dari 2 menit yang lalu atau belum pernah run

---

### 4. Test Command

1. Klik tombol **"Test"** pada command yang ingin ditest
2. Konfirmasi dengan klik **"Run"**
3. Sistem akan menjalankan command
4. Hasil akan ditampilkan dalam popup

**Command yang bisa ditest:**

-   `attendance:generate-absent` - Generate absensi alpha
-   `schedule:run` - Jalankan semua scheduled tasks
-   `schedule:list` - Lihat daftar scheduled tasks

---

### 5. Run Scheduler Manually

Untuk testing atau troubleshooting:

1. Klik tombol **"Run Scheduler Now"**
2. Konfirmasi
3. Scheduler akan dijalankan secara manual
4. Status akan di-update

---

### 6. View Schedule List

Melihat daftar semua scheduled tasks:

1. Klik **"View Schedule List"**
2. Modal akan menampilkan output dari `php artisan schedule:list`
3. Bisa lihat semua tasks, waktu, dan deskripsi

---

## 🔧 Troubleshooting

### Masalah 1: Cron Tidak Berjalan

**Gejala:**

-   Status menunjukkan "Inactive"
-   Last run tidak update

**Solusi:**

1. Pastikan cron sudah disetup di server
2. Periksa path PHP sudah benar
3. Periksa permission folder storage/logs:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

4. Cek log error di `storage/logs/laravel.log`

---

### Masalah 2: Command Error di cPanel

**Gejala:**

-   Email error dari cPanel
-   Command not found

**Solusi:**

1. Hubungi hosting provider untuk path PHP yang benar
2. Coba ganti path PHP:
    - `/usr/bin/php`
    - `/usr/local/bin/php`
    - `/opt/cpanel/ea-php82/root/usr/bin/php`
3. Test dengan command sederhana dulu:

```bash
* * * * * /usr/bin/php -v
```

---

### Masalah 3: Permission Denied

**Gejala:**

-   Error "Permission denied"
-   Cron tidak bisa menulis log

**Solusi:**

```bash
# Set permission yang benar
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

### Masalah 4: Windows Task Scheduler Tidak Jalan

**Gejala:**

-   Task terdaftar tapi tidak execute

**Solusi:**

1. Periksa path PHP benar
2. Gunakan full path untuk artisan:

```
C:\php\php.exe C:\xampp\htdocs\SistemAbsensiKaryawan\artisan schedule:run
```

3. Run as Administrator
4. Periksa Windows Event Viewer untuk error

---

### Masalah 5: Laravel Log "No scheduled commands are ready to run"

**Gejala:**

-   Cron jalan tapi command tidak execute

**Solusi:**

1. Ini normal! Artinya belum waktunya command berjalan
2. Command `attendance:generate-absent` hanya jalan pukul 23:59
3. Untuk test, ubah schedule di `routes/console.php`:

```php
// Untuk testing (jalan setiap menit)
Schedule::command('attendance:generate-absent')->everyMinute();

// Production (jalan pukul 23:59)
Schedule::command('attendance:generate-absent')->dailyAt('23:59');
```

---

## 📊 Monitoring & Maintenance

### Check Cron Log

```bash
# Linux
tail -f storage/logs/cron.log

# Windows
Get-Content storage/logs/cron.log -Tail 50 -Wait
```

### Test Manual

```bash
# Test scheduler
php artisan schedule:run

# Test specific command
php artisan attendance:generate-absent

# List all schedules
php artisan schedule:list
```

### Clear Cache (jika ada masalah)

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 🎯 Best Practices

1. **Setup Monitoring**

    - Check status cron secara berkala
    - Setup email notification untuk error

2. **Logging**

    - Enable logging untuk debug
    - Rotate log file secara berkala

3. **Testing**

    - Test command manual sebelum setup cron
    - Verifikasi hasil setelah setup

4. **Backup**

    - Backup setting cron
    - Dokumentasi command yang digunakan

5. **Security**
    - Jangan expose cron endpoint ke public
    - Restrict akses hanya untuk admin

---

## 📞 Support

Jika masih mengalami kesulitan:

1. Check dokumentasi Laravel Schedule: https://laravel.com/docs/scheduling
2. Hubungi hosting provider untuk bantuan setup cron
3. Contact developer/administrator sistem

---

## 📝 Changelog

### Version 1.0 (2025-10-22)

-   ✅ Initial release
-   ✅ Auto-detect environment
-   ✅ Multiple platform support
-   ✅ One-click copy command
-   ✅ Status monitoring
-   ✅ Manual test & run
-   ✅ Complete setup guides

---

**Dibuat dengan ❤️ untuk Sistem Absensi Karyawan**
