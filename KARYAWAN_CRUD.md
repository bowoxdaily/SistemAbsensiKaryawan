# CRUD Karyawan

## Overview

Modul CRUD Karyawan untuk mengelola data karyawan perusahaan dengan lengkap termasuk data pribadi, pekerjaan, dan kontak.

## Features

✅ Tampilan responsive (Desktop & Mobile)
✅ Form dengan tab navigation (Pribadi, Pekerjaan, Kontak)
✅ Pagination (10 data per halaman)
✅ Dropdown action menu (Detail, Edit, Delete)
✅ Validasi form lengkap
✅ Master data integration (Department & Position)
✅ Auto create user account saat tambah karyawan

## Files Created/Modified

### 1. Model

-   `app/Models/Karyawans.php` - Model dengan relasi ke Department, Position, User, dan Supervisor

### 2. Controller

-   `app/Http/Controllers/Admin/KaryawanController.php` - CRUD operations dan master data

### 3. View

-   `resources/views/admin/karyawan/index.blade.php` - Halaman utama dengan table dan form modal

### 4. Routes

-   `routes/api.php` - API routes untuk karyawan
-   `routes/web.php` - Web route untuk dashboard karyawan

## API Endpoints

### Get Master Data

```
GET /api/karyawan/master-data
Response: {
    "success": true,
    "data": {
        "departments": [...],
        "positions": [...],
        "supervisors": [...]
    }
}
```

### List Karyawan (Paginated)

```
GET /api/karyawan?page=1&per_page=10&search=keyword
```

### Create Karyawan

```
POST /api/karyawan
Body: {
    employee_code, nik, name, gender, birth_place, birth_date,
    marital_status, department_id, position_id, join_date,
    employment_status, shift_type, address, city, province,
    postal_code, phone, email, emergency_contact_name,
    emergency_contact_phone, status
}
```

### Show Karyawan

```
GET /api/karyawan/{id}
```

### Update Karyawan

```
PUT /api/karyawan/{id}
Body: (same as create)
```

### Delete Karyawan

```
DELETE /api/karyawan/{id}
```

## Field Validations

### Required Fields

-   employee_code (unique, max 20 char)
-   name (max 100 char)
-   gender (L/P)
-   birth_place, birth_date
-   marital_status (Belum Menikah, Menikah, Duda, Janda)
-   department_id, position_id
-   join_date
-   employment_status (Tetap, Kontrak, Magang, Outsource)
-   shift_type (Pagi, Sore, Malam, Rotasi)
-   address, city, province, postal_code
-   phone, email (unique)
-   emergency_contact_name, emergency_contact_phone
-   status (active, inactive, resign)

### Optional Fields

-   nik
-   supervisor_id
-   salary_base

## Default Behavior

-   Saat create karyawan, otomatis membuat user account dengan:
    -   name: sama dengan nama karyawan
    -   email: sama dengan email karyawan
    -   password: "password123" (default)
-   Saat delete karyawan, user account juga terhapus
-   Pagination default: 10 data per halaman

## UI Components

### Table Columns

1. # (row number)
2. Kode Karyawan
3. Nama
4. Departemen
5. Posisi
6. Status (badge: success/warning/danger)
7. Aksi (dropdown menu)

### Form Tabs

1. **Pribadi**: Kode, NIK, Nama, Gender, TTL, Status Perkawinan
2. **Pekerjaan**: Departemen, Posisi, Tgl Bergabung, Status Kerja, Shift, Status Karyawan
3. **Kontak**: Alamat lengkap, Kota, Provinsi, Kode Pos, HP, Email, Kontak Darurat

### Modal Detail

Menampilkan informasi lengkap karyawan dalam 2 kolom:

-   Kolom kiri: Identitas & Data Pekerjaan
-   Kolom kanan: Kontak & Kontak Darurat

## Access

-   Web: `/admin/karyawan`
-   Route name: `admin.karyawan.index`

## Dependencies

-   jQuery
-   Bootstrap 5
-   Boxicons
-   Department & Position data must exist

## Notes

-   Password default untuk user baru: `password123`
-   User harus mengubah password setelah login pertama kali
-   Karyawan dengan status 'resign' tidak bisa login
-   Supervisor list hanya menampilkan karyawan dengan status 'active'
