# CRUD Jabatan/Posisi - Dokumentasi

## Overview

Module CRUD untuk mengelola data jabatan/posisi karyawan di perusahaan.

## Database Schema

**Table:** `positions`

| Column      | Type                      | Description                          |
| ----------- | ------------------------- | ------------------------------------ |
| id          | bigint (PK)               | Primary key                          |
| code        | varchar(20) unique        | Kode jabatan (contoh: MGR, SPV, STF) |
| name        | varchar(100)              | Nama jabatan                         |
| description | text nullable             | Deskripsi jabatan                    |
| status      | enum('active','inactive') | Status jabatan (default: active)     |
| created_at  | timestamp                 | Waktu pembuatan                      |
| updated_at  | timestamp                 | Waktu update terakhir                |

## Files Created

### 1. Controller

**Path:** `app/Http/Controllers/Admin/PositionController.php`

**Methods:**

-   `dashboard()` - Display view halaman jabatan
-   `index()` - Get paginated list dengan search (API)
-   `store()` - Create jabatan baru dengan validasi
-   `show($id)` - Get detail jabatan
-   `update($id)` - Update data jabatan
-   `destroy($id)` - Delete jabatan (dengan proteksi jika masih digunakan)

### 2. Model

**Path:** `app/Models/Position.php`

**Fillable Fields:**

-   code
-   name
-   description
-   status

**Relations:**

-   `employees()` - HasMany ke Karyawans

### 3. View

**Path:** `resources/views/admin/positions/index.blade.php`

**Features:**

-   Responsive design (desktop table + mobile cards)
-   Modal form untuk create/edit
-   Detail modal untuk view
-   Dropdown menu dengan 3-dot icon
-   SweetAlert2 untuk konfirmasi delete
-   Pagination (10 items per page)
-   Empty state dengan call-to-action
-   Status badge (Aktif/Tidak Aktif)

### 4. Routes

**API Routes** (`routes/api.php`):

```php
GET    /api/positions        - List positions
POST   /api/positions        - Create position
GET    /api/positions/{id}   - Get position details
PUT    /api/positions/{id}   - Update position
DELETE /api/positions/{id}   - Delete position
```

**Web Routes** (`routes/web.php`):

```php
GET /admin/positions - Position dashboard (auth required)
```

### 5. Seeder

**Path:** `database/seeders/PositionSeeder.php`

**Default Data:**

1. DIR - Direktur
2. MGR - Manager
3. SPV - Supervisor
4. TL - Team Leader
5. SR - Senior Staff
6. STF - Staff
7. JR - Junior Staff
8. INT - Intern

## Validation Rules

### Create/Update Position

| Field       | Rules                            | Error Message                              |
| ----------- | -------------------------------- | ------------------------------------------ |
| code        | required, string, max:20, unique | Kode jabatan harus diisi / sudah digunakan |
| name        | required, string, max:100        | Nama jabatan harus diisi                   |
| description | nullable, string                 | -                                          |
| status      | required, in:active,inactive     | Status harus dipilih                       |

## Business Rules

1. **Unique Code:** Kode jabatan harus unique dalam sistem
2. **Delete Protection:** Jabatan tidak dapat dihapus jika masih digunakan oleh karyawan
3. **Status Management:** Jabatan dapat di-nonaktifkan tanpa menghapus data

## Usage

### Access Page

Login ke sistem, kemudian akses:

```
http://localhost:8000/admin/positions
```

### Create Position

1. Klik tombol "Tambah"
2. Isi form:
    - Kode Jabatan (required, unique)
    - Nama Jabatan (required)
    - Deskripsi (optional)
    - Status (required)
3. Klik "Simpan"

### Edit Position

1. Klik menu 3-dot pada row jabatan
2. Pilih "Edit"
3. Update data
4. Klik "Simpan"

### Delete Position

1. Klik menu 3-dot pada row jabatan
2. Pilih "Hapus"
3. Konfirmasi dengan SweetAlert
4. Sistem akan cek apakah jabatan masih digunakan

### View Details

1. Klik menu 3-dot pada row jabatan
2. Pilih "Detail"
3. Modal akan menampilkan informasi lengkap

## API Response Format

### Success Response

```json
{
  "success": true,
  "message": "Data jabatan berhasil dimuat",
  "data": {
    "current_page": 1,
    "data": [...],
    "total": 8
  }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Gagal menambahkan jabatan",
    "errors": {
        "code": ["Kode jabatan sudah digunakan"]
    }
}
```

## Integration

Module ini terintegrasi dengan:

-   **Karyawan Module:** Position digunakan sebagai foreign key di table employees
-   **Master Data API:** Position data diambil untuk dropdown di form karyawan

## Testing

### Run Seeder

```bash
php artisan db:seed PositionSeeder
```

### Check Data

```bash
php artisan tinker
>>> App\Models\Position::all();
```

### Test API

```bash
# Get all positions
curl http://localhost:8000/api/positions

# Get position by ID
curl http://localhost:8000/api/positions/1
```

## Notes

-   Default credentials: `admin@example.com` / `password`
-   Pagination default: 10 items per page
-   Search tersedia untuk field: name, code, description
-   Sort default: by name ascending
