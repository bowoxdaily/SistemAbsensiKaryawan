# Dokumentasi Database - Sistem Absensi Karyawan

## Struktur Database

Database ini dirancang sesuai dengan struktur yang diminta, terdiri dari beberapa tabel utama untuk mengelola data karyawan dan absensi.

---

## 📊 Tabel-Tabel Database

### 1. **users** (Tabel Akun Login)

Tabel untuk menyimpan data akun login user sistem.

| Kolom             | Tipe            | Deskripsi                                 |
| ----------------- | --------------- | ----------------------------------------- |
| id                | BIGINT (PK, AI) | Primary key                               |
| name              | VARCHAR(255)    | Nama user                                 |
| email             | VARCHAR(255)    | Email (unique)                            |
| email_verified_at | TIMESTAMP       | Tanggal verifikasi email                  |
| password          | VARCHAR(255)    | Password (hashed)                         |
| role              | ENUM            | Role user: `karyawan`, `admin`, `manager` |
| status            | ENUM            | Status: `aktif`, `nonaktif`               |
| avatar            | VARCHAR(255)    | Path foto profil user                     |
| remember_token    | VARCHAR(100)    | Token untuk remember me                   |
| created_at        | TIMESTAMP       | Waktu dibuat                              |
| updated_at        | TIMESTAMP       | Waktu diupdate                            |

**Default Users:**

-   Admin: admin@example.com / password
-   Manager: manager@example.com / password
-   Karyawan: karyawan@example.com / password

---

### 2. **departments** (Departemen)

Tabel untuk menyimpan data departemen perusahaan.

| Kolom       | Tipe            | Deskripsi            |
| ----------- | --------------- | -------------------- |
| id          | BIGINT (PK, AI) | Primary key          |
| name        | VARCHAR(100)    | Nama departemen      |
| description | TEXT            | Deskripsi departemen |
| created_at  | TIMESTAMP       | Waktu dibuat         |
| updated_at  | TIMESTAMP       | Waktu diupdate       |

**Data Default:**

-   Human Resources
-   IT & Development
-   Finance & Accounting
-   Marketing
-   Operations
-   Sales

---

### 3. **positions** (Jabatan)

Tabel untuk menyimpan data jabatan/posisi karyawan.

| Kolom       | Tipe            | Deskripsi         |
| ----------- | --------------- | ----------------- |
| id          | BIGINT (PK, AI) | Primary key       |
| name        | VARCHAR(100)    | Nama jabatan      |
| description | TEXT            | Deskripsi jabatan |
| created_at  | TIMESTAMP       | Waktu dibuat      |
| updated_at  | TIMESTAMP       | Waktu diupdate    |

**Data Default:**

-   Director
-   Manager
-   Supervisor
-   Staff
-   Intern

---

### 4. **employees** (Data Karyawan) ⭐

Tabel utama untuk menyimpan data karyawan lengkap sesuai screenshot.

#### 🔹 1. Identitas Pribadi

| Kolom          | Tipe            | Deskripsi                                                      |
| -------------- | --------------- | -------------------------------------------------------------- |
| id             | BIGINT (PK, AI) | Primary key                                                    |
| employee_code  | VARCHAR(20)     | Kode unik (misal: EMP001) - UNIQUE                             |
| nik            | VARCHAR(20)     | Nomor Induk Kependudukan (opsional)                            |
| name           | VARCHAR(100)    | Nama lengkap karyawan                                          |
| gender         | ENUM('L','P')   | Jenis kelamin                                                  |
| birth_place    | VARCHAR(50)     | Tempat lahir                                                   |
| birth_date     | DATE            | Tanggal lahir                                                  |
| marital_status | ENUM            | Status perkawinan: `Belum Menikah`, `Menikah`, `Duda`, `Janda` |

#### 🔹 2. Data Pekerjaan

| Kolom             | Tipe                  | Deskripsi                                               |
| ----------------- | --------------------- | ------------------------------------------------------- |
| department_id     | BIGINT (FK)           | Departemen tempat karyawan bekerja                      |
| position_id       | BIGINT (FK)           | Jabatan/posisi karyawan                                 |
| join_date         | DATE                  | Tanggal bergabung                                       |
| employment_status | ENUM                  | Status kerja: `Tetap`, `Kontrak`, `Magang`, `Outsource` |
| shift_type        | ENUM                  | Jenis shift: `Pagi`, `Sore`, `Malam`, `Rotasi`          |
| supervisor_id     | BIGINT (FK, nullable) | ID atasan langsung (jika ada)                           |
| salary_base       | DECIMAL(12,2)         | Gaji pokok (opsional, kalau nanti mau hitung payroll)   |

#### 🔹 3. Data Kontak & Alamat

| Kolom                   | Tipe         | Deskripsi                                   |
| ----------------------- | ------------ | ------------------------------------------- |
| address                 | TEXT         | Alamat lengkap                              |
| city                    | VARCHAR(50)  | Kota domisili                               |
| province                | VARCHAR(50)  | Provinsi                                    |
| postal_code             | VARCHAR(10)  | Kode pos                                    |
| phone                   | VARCHAR(20)  | Nomor HP                                    |
| email                   | VARCHAR(100) | Email pribadi (bisa sama dengan akun login) |
| emergency_contact_name  | VARCHAR(100) | Nama kontak darurat                         |
| emergency_contact_phone | VARCHAR(20)  | Nomor kontak darurat                        |

#### 🔹 4. Data Akun & Sistem

| Kolom         | Tipe         | Deskripsi                              |
| ------------- | ------------ | -------------------------------------- |
| user_id       | BIGINT (FK)  | Relasi ke tabel users (akun login)     |
| status        | ENUM         | Status: `active`, `inactive`, `resign` |
| profile_photo | VARCHAR(255) | Path foto profil                       |
| created_at    | TIMESTAMP    | Waktu dibuat                           |
| updated_at    | TIMESTAMP    | Waktu diupdate                         |

**Foreign Keys:**

-   `department_id` → `departments.id` (ON DELETE RESTRICT)
-   `position_id` → `positions.id` (ON DELETE RESTRICT)
-   `supervisor_id` → `employees.id` (ON DELETE SET NULL)
-   `user_id` → `users.id` (ON DELETE CASCADE)

---

### 5. **work_schedules** (Jadwal Kerja)

Tabel untuk menyimpan jadwal kerja/shift.

| Kolom          | Tipe            | Deskripsi                       |
| -------------- | --------------- | ------------------------------- |
| id             | BIGINT (PK, AI) | Primary key                     |
| name           | VARCHAR(100)    | Nama jadwal (misal: Shift Pagi) |
| start_time     | TIME            | Jam mulai kerja                 |
| end_time       | TIME            | Jam selesai kerja               |
| late_tolerance | INTEGER         | Toleransi keterlambatan (menit) |
| is_active      | BOOLEAN         | Status aktif jadwal             |
| created_at     | TIMESTAMP       | Waktu dibuat                    |
| updated_at     | TIMESTAMP       | Waktu diupdate                  |

**Data Default:**

-   Shift Pagi: 08:00 - 16:00 (toleransi 15 menit)
-   Shift Siang: 12:00 - 20:00 (toleransi 15 menit)
-   Shift Malam: 20:00 - 04:00 (toleransi 15 menit)

---

### 6. **attendances** (Absensi)

Tabel untuk menyimpan data absensi karyawan.

| Kolom           | Tipe            | Deskripsi                                                      |
| --------------- | --------------- | -------------------------------------------------------------- |
| id              | BIGINT (PK, AI) | Primary key                                                    |
| employee_id     | BIGINT (FK)     | ID karyawan                                                    |
| attendance_date | DATE            | Tanggal absensi                                                |
| check_in        | TIME            | Jam masuk                                                      |
| check_out       | TIME            | Jam keluar                                                     |
| status          | ENUM            | Status: `hadir`, `terlambat`, `izin`, `sakit`, `alpha`, `cuti` |
| notes           | TEXT            | Catatan tambahan                                               |
| photo_in        | VARCHAR(255)    | Foto saat check in                                             |
| photo_out       | VARCHAR(255)    | Foto saat check out                                            |
| location_in     | VARCHAR(255)    | Lokasi GPS saat check in                                       |
| location_out    | VARCHAR(255)    | Lokasi GPS saat check out                                      |
| late_minutes    | INTEGER         | Menit keterlambatan                                            |
| created_at      | TIMESTAMP       | Waktu dibuat                                                   |
| updated_at      | TIMESTAMP       | Waktu diupdate                                                 |

**Foreign Key:**

-   `employee_id` → `employees.id` (ON DELETE CASCADE)

**Index:**

-   Composite index pada `employee_id` dan `attendance_date`

---

### 7. **leaves** (Cuti/Izin)

Tabel untuk menyimpan pengajuan cuti atau izin karyawan.

| Kolom            | Tipe                  | Deskripsi                                 |
| ---------------- | --------------------- | ----------------------------------------- |
| id               | BIGINT (PK, AI)       | Primary key                               |
| employee_id      | BIGINT (FK)           | ID karyawan                               |
| leave_type       | ENUM                  | Jenis: `cuti`, `izin`, `sakit`            |
| start_date       | DATE                  | Tanggal mulai                             |
| end_date         | DATE                  | Tanggal selesai                           |
| total_days       | INTEGER               | Total hari                                |
| reason           | TEXT                  | Alasan cuti/izin                          |
| attachment       | VARCHAR(255)          | Lampiran dokumen (jika ada)               |
| status           | ENUM                  | Status: `pending`, `approved`, `rejected` |
| approved_by      | BIGINT (FK, nullable) | Disetujui oleh (user_id)                  |
| approved_at      | TIMESTAMP             | Tanggal disetujui                         |
| rejection_reason | TEXT                  | Alasan penolakan                          |
| created_at       | TIMESTAMP             | Waktu dibuat                              |
| updated_at       | TIMESTAMP             | Waktu diupdate                            |

**Foreign Keys:**

-   `employee_id` → `employees.id` (ON DELETE CASCADE)
-   `approved_by` → `users.id` (ON DELETE SET NULL)

**Index:**

-   Composite index pada `employee_id`, `start_date`, dan `end_date`

---

## 🔐 Relasi Antar Tabel

```
users (1) ──< (1) employees
departments (1) ──< (*) employees
positions (1) ──< (*) employees
employees (1) ──< (*) employees (supervisor)
employees (1) ──< (*) attendances
employees (1) ──< (*) leaves
users (1) ──< (*) leaves (approved_by)
```

---

## 📝 Catatan Penting

1. **Foreign Key Constraints:**

    - `RESTRICT`: Tidak boleh hapus parent jika masih ada child
    - `CASCADE`: Hapus child jika parent dihapus
    - `SET NULL`: Set NULL pada child jika parent dihapus

2. **Indexes:**

    - Semua primary keys otomatis terindex
    - Foreign keys otomatis terindex
    - Composite index ditambahkan pada tabel yang sering di-query

3. **Enum Values:**

    - Pastikan value yang diinput sesuai dengan pilihan yang ada
    - Gunakan value yang case-sensitive sesuai definisi

4. **Timestamps:**
    - Semua tabel menggunakan `created_at` dan `updated_at`
    - Laravel otomatis mengisi timestamp ini

---

## 🚀 Cara Menjalankan

```bash
# Jalankan migrasi
php artisan migrate:fresh

# Jalankan seeder (isi data awal)
php artisan db:seed

# Atau gabungkan keduanya
php artisan migrate:fresh --seed
```

---

## 📦 Data Default Setelah Seeding

### Users:

1. **Admin** - admin@example.com / password
2. **Manager** - manager@example.com / password
3. **Karyawan** - karyawan@example.com / password

### Departments:

-   Human Resources
-   IT & Development
-   Finance & Accounting
-   Marketing
-   Operations
-   Sales

### Positions:

-   Director
-   Manager
-   Supervisor
-   Staff
-   Intern

### Work Schedules:

-   Shift Pagi (08:00-16:00)
-   Shift Siang (12:00-20:00)
-   Shift Malam (20:00-04:00)

---

## 🔄 Update Database (Jika Ada Perubahan)

```bash
# Rollback 1 migration
php artisan migrate:rollback

# Rollback semua dan jalankan ulang
php artisan migrate:fresh

# Dengan seeder
php artisan migrate:fresh --seed
```

---

## 💾 Backup Database

Gunakan tools seperti:

-   **phpMyAdmin** - Export SQL
-   **MySQL Workbench** - Data Export
-   **Command Line**:
    ```bash
    mysqldump -u root -p absensi > backup.sql
    ```

---

## 📊 ERD (Entity Relationship Diagram)

Untuk visualisasi yang lebih baik, Anda bisa generate ERD menggunakan:

-   MySQL Workbench (Reverse Engineer)
-   dbdiagram.io
-   draw.io

---

Dokumentasi ini dibuat sesuai dengan struktur screenshot yang diberikan. Semua field dan relasi telah diimplementasikan dengan lengkap.
