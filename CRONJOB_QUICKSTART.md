# Quick Start - Setup Cron Job 🚀

## Akses Menu

**Dashboard → Pengaturan → Cron Job**

---

## 📋 Langkah Cepat

### 1️⃣ Buka Halaman Cron Job

-   Login sebagai Admin
-   Klik menu **Pengaturan** di sidebar
-   Pilih **Cron Job**

### 2️⃣ Copy Command

-   Pilih platform hosting Anda (Linux/Windows)
-   Klik tombol **Copy** 📋
-   Command otomatis ter-copy ke clipboard

### 3️⃣ Paste ke Hosting

#### Untuk cPanel:

1. Buka **Cron Jobs** di cPanel
2. Pilih: **Once Per Minute (\*\***)\*\*
3. Paste command yang sudah dicopy
4. Klik **Add New Cron Job**
5. ✅ Selesai!

#### Untuk Plesk:

1. Buka **Scheduled Tasks**
2. Klik **Add Task**
3. Schedule: `*/1 * * * *`
4. Paste command
5. ✅ Selesai!

#### Untuk VPS:

```bash
crontab -e
# Paste command
# Save dengan :wq
```

### 4️⃣ Verifikasi

-   Tunggu 1-2 menit
-   Klik **Check Status** di halaman Cron Job
-   Status harus menunjukkan 🟢 **Active**

---

## 🎯 Yang Akan Berjalan Otomatis

| Command                      | Jadwal            | Fungsi                                        |
| ---------------------------- | ----------------- | --------------------------------------------- |
| `attendance:generate-absent` | Setiap hari 23:59 | Generate absensi alpha untuk yang tidak hadir |

---

## ❓ Troubleshooting Cepat

### Status Inactive?

1. ✅ Pastikan cron sudah ditambahkan di hosting
2. ✅ Tunggu 1-2 menit
3. ✅ Refresh status dengan tombol **Check Status**

### Error di cPanel?

-   Hubungi hosting provider untuk **path PHP yang benar**
-   Biasanya: `/usr/bin/php` atau `/usr/local/bin/php`

### Butuh Bantuan?

-   Lihat panduan lengkap di **CRONJOB_SETUP.md**
-   Tab **Panduan Setup** di halaman Cron Job
-   Hubungi administrator sistem

---

## ⚡ Fitur Tambahan

### Test Manual

Klik **Run Scheduler Now** untuk test manual tanpa menunggu jadwal

### View Schedule

Klik **View Schedule List** untuk melihat semua task terjadwal

---

**🎉 Selamat! Cron Job Anda sudah aktif!**
