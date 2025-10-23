# 📋 Logika Auto-Generate Alpha - Sistem Absensi Karyawan

## 🎯 Ringkasan

Sistem akan **otomatis menandai karyawan sebagai ALPHA** jika mereka tidak melakukan check-in dan sudah **melewati jam checkout + 30 menit grace period**.

---

## ⏰ Cara Kerja

### 1. **Schedule Cron Job**

```
Setiap jam (08:00 - 23:59)
Senin - Jumat (weekdays)
```

### 2. **Alur Pengecekan**

```
┌─────────────────────────────────────┐
│  Cron berjalan setiap jam           │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Ambil semua karyawan aktif         │
│  dengan work schedule               │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Cek apakah hari ini hari kerja?    │
│  (Skip weekend)                     │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Ambil jam checkout dari schedule   │
│  Tambahkan 30 menit grace period    │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Apakah sekarang sudah melewati     │
│  checkout + 30 menit?               │
└──────────────┬──────────────────────┘
               │
        ┌──────┴──────┐
        │             │
        ▼             ▼
      TIDAK          YA
        │             │
        │             ▼
        │    ┌─────────────────────┐
        │    │ Ada attendance      │
        │    │ record hari ini?    │
        │    └─────────┬───────────┘
        │              │
        │       ┌──────┴──────┐
        │       │             │
        │       ▼             ▼
        │     TIDAK          ADA
        │       │             │
        │       │             └──▶ Skip
        │       │
        │       ▼
        │    ┌─────────────────────┐
        │    │ Generate ALPHA      │
        │    │ attendance          │
        │    └─────────────────────┘
        │
        ▼
     Skip
```

---

## 📝 Contoh Kasus

### **Kasus 1: Shift Pagi**

**Setting Shift:**

-   Start: 08:00
-   End: 16:00

**Timeline:**

```
08:00 ─── Jam Masuk
   │
   │  [Karyawan tidak check-in]
   │
16:00 ─── Jam Checkout
   │
   │  [Grace Period 30 menit]
   │
16:30 ─── Batas Grace Period
   │
17:00 ─── Cron Check (Hourly)
   │      ✅ Sudah lewat 16:30
   │      ✅ Belum ada attendance
   │      ➡️  GENERATE ALPHA
```

**Hasil:**

-   Karyawan ditandai **ALPHA** pada jam 17:00
-   Notes: "Auto-generated: Tidak melakukan absensi (melewati jam checkout + 30 menit)"

---

### **Kasus 2: Shift Siang**

**Setting Shift:**

-   Start: 13:00
-   End: 21:00

**Timeline:**

```
13:00 ─── Jam Masuk
   │
   │  [Karyawan tidak check-in]
   │
21:00 ─── Jam Checkout
   │
   │  [Grace Period 30 menit]
   │
21:30 ─── Batas Grace Period
   │
22:00 ─── Cron Check (Hourly)
   │      ✅ Sudah lewat 21:30
   │      ✅ Belum ada attendance
   │      ➡️  GENERATE ALPHA
```

---

### **Kasus 3: Karyawan Check-in Tapi Terlambat**

**Setting Shift:**

-   Start: 08:00
-   End: 16:00

**Timeline:**

```
08:00 ─── Jam Masuk
   │
   │  [Karyawan tidak check-in]
   │
09:30 ─── Karyawan Check-in (Terlambat 1.5 jam)
   │      ✅ Attendance record dibuat
   │      ✅ Status: TERLAMBAT
   │
16:00 ─── Jam Checkout
   │
17:00 ─── Cron Check
   │      ❌ Sudah ada attendance
   │      ➡️  SKIP (tidak generate alpha)
```

**Hasil:**

-   Karyawan **TIDAK** ditandai alpha
-   Sudah ada record dengan status **TERLAMBAT**

---

### **Kasus 4: Weekend**

**Timeline:**

```
Sabtu/Minggu
   │
17:00 ─── Cron Check
   │      ❌ Hari weekend
   │      ➡️  SKIP semua karyawan
```

**Hasil:**

-   Tidak ada pengecekan di weekend
-   Semua karyawan di-skip

---

## ⚙️ Konfigurasi

### **Grace Period**

Default: **30 menit** setelah jam checkout

Untuk mengubah grace period, edit file:
`app/Console/Commands/GenerateAbsentAttendance.php`

```php
// Line ~75
$gracePeriodEnd = $checkoutDateTime->copy()->addMinutes(30); // Ubah 30 ke nilai lain
```

---

### **Jam Operasional Cron**

Default: **08:00 - 23:59** (Senin-Jumat)

Untuk mengubah, edit file:
`routes/console.php`

```php
Schedule::command('attendance:generate-absent')
    ->hourly()
    ->between('08:00', '23:59')  // Ubah jam di sini
    ->weekdays()                  // Atau ubah ke ->daily() untuk setiap hari
```

---

## 🧪 Testing

### **Test Manual**

1. **Test untuk hari ini:**

```bash
php artisan attendance:generate-absent
```

2. **Test untuk tanggal tertentu:**

```bash
php artisan attendance:generate-absent 2025-10-23
```

3. **Lihat output detail:**

```bash
php artisan attendance:generate-absent -v
```

---

### **Skenario Test**

#### Test 1: Karyawan Belum Check-in

```bash
# 1. Pastikan ada karyawan aktif dengan work schedule
# 2. Pastikan sudah melewati jam checkout + 30 menit
# 3. Jalankan command
php artisan attendance:generate-absent

# Expected: Generate alpha untuk karyawan tersebut
```

#### Test 2: Karyawan Sudah Check-in

```bash
# 1. Pastikan karyawan sudah punya attendance record hari ini
# 2. Jalankan command
php artisan attendance:generate-absent

# Expected: Skip karyawan tersebut (tidak generate alpha)
```

#### Test 3: Belum Melewati Grace Period

```bash
# 1. Pastikan waktu sekarang BELUM melewati checkout + 30 menit
# 2. Jalankan command
php artisan attendance:generate-absent

# Expected: Skip semua karyawan
```

---

## 📊 Monitoring

### **Check Schedule List**

```bash
php artisan schedule:list
```

Output:

```
0 * * * 1-5  php artisan attendance:generate-absent
```

Artinya:

-   `0 * * * 1-5`: Setiap jam di menit ke-0, Senin-Jumat
-   Contoh: 08:00, 09:00, 10:00, ..., 23:00

---

### **Check Last Run**

```bash
# Lihat log Laravel
tail -f storage/logs/laravel.log

# Atau lihat cron log (jika disetup)
tail -f storage/logs/cron.log
```

---

### **Via Dashboard Admin**

1. Login sebagai Admin
2. Buka **Pengaturan → Cron Job**
3. Klik **"Check Status"**
4. Lihat:
    - Last Run Time
    - Next Run Time
    - Status (Active/Inactive)

---

## 🔍 Troubleshooting

### **Problem 1: Karyawan Tidak Di-generate Alpha**

**Kemungkinan Penyebab:**

1. ✅ Belum melewati grace period (checkout + 30 menit)
2. ✅ Sudah ada attendance record
3. ✅ Hari ini bukan hari kerja (weekend)
4. ✅ Karyawan tidak punya work schedule
5. ✅ Work schedule tidak aktif untuk hari ini

**Solusi:**

```bash
# Debug dengan verbose mode
php artisan attendance:generate-absent -v

# Cek work schedule karyawan
php artisan tinker
>>> $employee = Employee::find(1);
>>> $employee->workSchedule;
>>> $employee->workSchedule->end_time;
```

---

### **Problem 2: Generate Alpha Terlalu Cepat**

**Penyebab:**
Grace period terlalu pendek

**Solusi:**
Ubah grace period di `GenerateAbsentAttendance.php`:

```php
// Dari 30 menit menjadi 60 menit
$gracePeriodEnd = $checkoutDateTime->copy()->addMinutes(60);
```

---

### **Problem 3: Generate Alpha di Weekend**

**Penyebab:**
Schedule tidak di-filter weekdays

**Solusi:**
Pastikan di `routes/console.php` ada `->weekdays()`:

```php
Schedule::command('attendance:generate-absent')
    ->hourly()
    ->between('08:00', '23:59')
    ->weekdays()  // ← Pastikan ini ada
```

---

## 📈 Best Practices

### 1. **Set Grace Period yang Wajar**

-   Terlalu pendek: Karyawan bisa ter-alpha padahal baru telat sedikit
-   Terlalu panjang: Delay detection terlalu lama
-   **Recommended**: 30-60 menit

### 2. **Monitor Log Regularly**

```bash
# Setup log rotation
# /etc/logrotate.d/laravel-cron

/path/to/app/storage/logs/cron.log {
    daily
    rotate 7
    compress
    missingok
    notifempty
}
```

### 3. **Setup Notification (Future)**

```php
// Kirim notifikasi ke admin setiap generate alpha
->after(function () {
    if ($generatedCount > 0) {
        Notification::send($admin, new AlphaGenerated($generatedCount));
    }
});
```

### 4. **Backup Sebelum Production**

```bash
# Backup database
php artisan backup:run

# Test dulu di development
APP_ENV=local php artisan attendance:generate-absent
```

---

## 🔐 Security Considerations

1. **Validasi Work Schedule**

    - Pastikan setiap karyawan punya work schedule yang valid
    - Cek apakah end_time format HH:mm:ss

2. **Race Condition**

    - Command sudah handle check attendance existing
    - Tidak akan double-generate alpha

3. **Permission**
    - Command hanya bisa dijalankan via cron/artisan
    - Tidak exposed via web route

---

## 📚 References

-   Laravel Scheduling: https://laravel.com/docs/scheduling
-   Carbon DateTime: https://carbon.nesbot.com/docs/
-   Cron Expression: https://crontab.guru/

---

## 📞 Support

Jika ada pertanyaan atau issue:

1. Check log di `storage/logs/laravel.log`
2. Test manual dengan verbose mode
3. Hubungi administrator sistem

---

**Updated:** October 23, 2025
**Version:** 2.0 - Hourly Check with Grace Period
