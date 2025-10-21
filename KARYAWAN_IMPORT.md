# Import Karyawan dari Excel - Dokumentasi

## Overview

Fitur untuk mengimport data karyawan dari file Excel (.xlsx/.xls) secara batch dengan validasi otomatis.

## Files Created/Modified

### 1. Import Class

**Path:** `app/Imports/KaryawanImport.php`

**Implements:**

-   `ToModel` - Convert row Excel ke model
-   `WithHeadingRow` - Gunakan baris pertama sebagai heading
-   `WithValidation` - Validasi setiap row
-   `SkipsOnError` - Skip row yang error, lanjutkan import
-   `SkipsOnFailure` - Skip row yang gagal validasi

**Features:**

-   ✅ Cache Department & Position untuk performa
-   ✅ Auto create user account dengan password default
-   ✅ Transaction untuk data integrity
-   ✅ Convert format (gender, status, date)
-   ✅ Handle Excel date format
-   ✅ Skip duplicate entries

### 2. Template Export Class

**Path:** `app/Exports/KaryawanTemplateExport.php`

**Features:**

-   ✅ Header dengan background hijau
-   ✅ Baris contoh data (row 2 - background hijau muda)
-   ✅ Baris catatan/notes (row 3 - background kuning)
-   ✅ Column width otomatis
-   ✅ Format yang user-friendly
-   ✅ **Excel dropdowns untuk data validation**
-   ✅ **Departemen & Posisi dari database (auto-update)**
-   ✅ **Dropdown untuk Jenis Kelamin, Status Perkawinan, Status Kerja, Jenis Shift, Status Karyawan**

**Dropdown Implementation:**

-   Uses `PhpSpreadsheet\Cell\DataValidation`
-   Applied to rows 2-1000
-   Error messages for invalid input
-   Prompt messages on cell selection
-   Department & Position loaded from active database records

### 3. Controller Methods

**Path:** `app/Http/Controllers/Admin/KaryawanController.php`

**Methods Added:**

```php
public function import(Request $request)
public function downloadTemplate()
```

### 4. Routes

**Path:** `routes/web.php`

```php
POST /admin/karyawan/import       - Import data
GET  /admin/karyawan/template     - Download template
```

### 5. View Components

**Path:** `resources/views/admin/karyawan/index.blade.php`

**Added:**

-   Button "Import" (biru, icon upload)
-   Modal import dengan form upload
-   Download template link
-   Progress indicator
-   JavaScript function `importKaryawan()`

## Template Excel Structure

### Header Row (Row 1)

Background hijau (#4CAF50), text putih, bold

| Column | Field                 |
| ------ | --------------------- |
| A      | Kode Karyawan         |
| B      | NIK                   |
| C      | Nama Lengkap          |
| D      | Jenis Kelamin         |
| E      | Tempat Lahir          |
| F      | Tanggal Lahir         |
| G      | Status Perkawinan     |
| H      | Departemen            |
| I      | Posisi                |
| J      | Tanggal Bergabung     |
| K      | Status Kerja          |
| L      | Jenis Shift           |
| M      | Status                |
| N      | Alamat                |
| O      | Kota                  |
| P      | Provinsi              |
| Q      | Kode Pos              |
| R      | No. HP                |
| S      | Email                 |
| T      | Kontak Darurat (Nama) |
| U      | Kontak Darurat (No)   |

### Example Row (Row 2)

Background hijau muda (#E8F5E9)

-   Berisi contoh data valid

### Notes Row (Row 3)

Background kuning (#FFF9C4), italic, font kecil

-   Berisi petunjuk format untuk setiap kolom
-   Kolom dengan dropdown menampilkan "Pilih dari dropdown ⬇"

## Excel Dropdown Fields

Template dilengkapi dengan dropdown validation untuk memudahkan input dan mencegah kesalahan:

### 1. Jenis Kelamin (Column D)

**Dropdown Options:**

-   Laki-laki
-   Perempuan

**Error Message:** "Pilih dari dropdown yang tersedia"
**Prompt:** "Pilih Jenis Kelamin"

### 2. Status Perkawinan (Column G)

**Dropdown Options:**

-   Belum Menikah
-   Menikah
-   Duda
-   Janda

**Error Message:** "Pilih dari dropdown yang tersedia"
**Prompt:** "Pilih Status Perkawinan"

### 3. Departemen (Column H)

**Dropdown Options:** Loaded from database (active departments only)

-   Data dinamis dari tabel `departments`
-   Auto-update saat ada departemen baru

**Error Message:** "Pilih dari dropdown yang tersedia"
**Prompt:** "Pilih Departemen"

### 4. Posisi (Column I)

**Dropdown Options:** Loaded from database (active positions only)

-   Data dinamis dari tabel `positions`
-   Auto-update saat ada posisi baru

**Error Message:** "Pilih dari dropdown yang tersedia"
**Prompt:** "Pilih Posisi/Jabatan"

### 5. Status Kerja (Column K)

**Dropdown Options:**

-   Tetap
-   Kontrak
-   Magang
-   Outsource

**Error Message:** "Pilih dari dropdown yang tersedia"
**Prompt:** "Pilih Status Kerja"

### 6. Jenis Shift (Column L)

**Dropdown Options:**

-   Pagi
-   Sore
-   Malam
-   Rotasi

**Error Message:** "Pilih dari dropdown yang tersedia"
**Prompt:** "Pilih Jenis Shift"

### 7. Status Karyawan (Column M)

**Dropdown Options:**

-   Aktif
-   Tidak Aktif
-   Resign

**Error Message:** "Pilih dari dropdown yang tersedia"
**Prompt:** "Pilih Status Karyawan"

### Dropdown Coverage

-   Applied to **rows 2-1000**
-   Prevents typos and invalid data
-   Shows error message if user types manually
-   Shows helpful prompt when cell is selected
-   Department & Position lists always up-to-date from database

## Validation Rules

| Field             | Rules                           |
| ----------------- | ------------------------------- |
| kode_karyawan     | required, unique                |
| nama_lengkap      | required                        |
| jenis_kelamin     | required                        |
| email             | required, email, unique         |
| departemen        | required, harus ada di database |
| posisi            | required, harus ada di database |
| tanggal_lahir     | required, format date           |
| tanggal_bergabung | required, format date           |

## Format Data

### Jenis Kelamin

Accepted values (case-insensitive):

-   Laki-laki: `L`, `Laki-laki`, `Laki`, `Male`
-   Perempuan: `P`, `Perempuan`, `Wanita`, `Female`

### Status Perkawinan

-   `Belum Menikah`
-   `Menikah`
-   `Duda`
-   `Janda`

### Status Kerja

-   `Tetap`
-   `Kontrak`
-   `Magang`
-   `Outsource`

### Jenis Shift

-   `Pagi`
-   `Sore`
-   `Malam`
-   `Rotasi`

### Status Karyawan

Accepted values:

-   Aktif: `Aktif`, `Active`
-   Tidak Aktif: `Tidak Aktif`, `Inactive`, `Nonaktif`
-   Resign: `Resign`, `Keluar`

### Format Tanggal

-   Preferred: `YYYY-MM-DD` (2025-01-15)
-   Also accepts: Common date formats (auto converted)
-   Excel date number format (auto detected)

## Usage Flow

### 1. Download Template

1. Klik tombol "Import" (biru)
2. Di modal, klik "Download Template Excel"
3. File `Template_Import_Karyawan.xlsx` akan terdownload

### 2. Fill Template

1. Buka template di Excel
2. **Hapus row 2 & 3** (contoh dan catatan) - atau mulai isi dari row 4
3. **Gunakan dropdown** untuk field yang tersedia (Jenis Kelamin, Status Perkawinan, Departemen, Posisi, Status Kerja, Jenis Shift, Status)
4. Isi data karyawan mulai dari row yang kosong
5. Untuk field dropdown:
    - Klik cell → dropdown arrow akan muncul
    - Pilih dari list yang tersedia
    - **JANGAN ketik manual** untuk field dropdown
6. Untuk field text (Nama, Alamat, dll) - ketik langsung
7. Format tanggal: `YYYY-MM-DD` (contoh: `2025-01-15`)
8. Save file

### 3. Import Data

1. Klik tombol "Import"
2. Klik "Pilih File Excel"
3. Select file yang sudah diisi
4. Klik "Import Data"
5. Tunggu proses selesai

### 4. Result

**Success:**

-   Modal tertutup
-   SweetAlert success muncul
-   Data karyawan otomatis reload
-   File input reset

**Error:**

-   SweetAlert error dengan detail error
-   List baris yang error
-   Data yang valid tetap diimport (skip on error)

## Error Handling

### Validation Errors

```
Baris 5: Kode karyawan sudah terdaftar
Baris 7: Format email tidak valid
Baris 10: Departemen tidak ditemukan
```

### Skip Strategy

-   Row yang error: **SKIP**, continue import
-   Row yang valid: **INSERT** ke database
-   Transaction per row untuk data integrity

### Common Errors

#### 1. Kode Karyawan Duplicate

**Error:** "Kode karyawan sudah terdaftar"

**Solution:**

-   Check existing data
-   Use unique employee code

#### 2. Email Duplicate

**Error:** "Email sudah terdaftar"

**Solution:**

-   Check existing users
-   Use unique email per employee

#### 3. Department/Position Not Found

**Error:** "Departemen tidak ditemukan"

**Solution:**

-   Pastikan nama departemen exact match
-   Buat departemen/posisi terlebih dahulu
-   Check typo di nama

#### 4. Invalid Date Format

**Error:** "Format tanggal tidak valid"

**Solution:**

-   Use `YYYY-MM-DD` format
-   Or use Excel date picker
-   Example: `2025-01-15`

#### 5. File Too Large

**Error:** "Ukuran file maksimal 2MB"

**Solution:**

-   Split data ke multiple files
-   Remove unnecessary formatting
-   Save as Excel binary (.xlsb) for smaller size

## Performance

### Optimizations Applied

-   ✅ Department & Position cache (avoid repeated queries)
-   ✅ Batch validation
-   ✅ Skip on error (continue processing)
-   ✅ Transaction per row (rollback on individual error)

### Recommended Limits

-   **< 100 rows:** Instant (1-2 seconds)
-   **100-500 rows:** Fast (5-10 seconds)
-   **500-1000 rows:** Moderate (20-30 seconds)
-   **> 1000 rows:** Consider chunking or background job

## Default Values

### Auto Generated

-   **Password:** `password123` (for user account)
-   **User Role:** Regular user (no admin privileges)

### Fallback Values

-   **Gender:** Default to `L` if invalid
-   **Status:** Default to `active` if invalid

## Testing

### Test Case 1: Valid Data

```
EMP001 | 1234567890 | John Doe | L | Jakarta | 1990-01-15 | ...
```

**Expected:** Success insert

### Test Case 2: Duplicate Code

```
EMP001 | ... (existing code)
```

**Expected:** Skip with error message

### Test Case 3: Invalid Email

```
... | invalid-email | ...
```

**Expected:** Skip with error message

### Test Case 4: Missing Required Field

```
| | John Doe | ... (missing code)
```

**Expected:** Skip with error message

### Test Case 5: Department Not Exists

```
... | Non-Existing Dept | ...
```

**Expected:** Skip with error message

## Security

### File Validation

-   ✅ Only accept .xlsx, .xls
-   ✅ Max file size: 2MB
-   ✅ CSRF token protection
-   ✅ Auth middleware required

### Data Validation

-   ✅ Email format validation
-   ✅ Unique constraints (code, email)
-   ✅ Required field validation
-   ✅ Foreign key validation (department, position)

### Database

-   ✅ Transaction per row
-   ✅ Auto rollback on error
-   ✅ User account created with hashed password

## Troubleshooting

### Import tidak berjalan

**Check:**

1. File format (.xlsx atau .xls)
2. File size (< 2MB)
3. CSRF token valid
4. User authenticated

### Data tidak masuk semua

**Check:**

1. Validation errors di response
2. Duplicate entries
3. Foreign key (department/position exists)
4. Format data sesuai template

### Error "Column not found"

**Solution:**

-   Pastikan header row exact match dengan template
-   Jangan ubah nama kolom
-   Download template terbaru

### Slow import

**Optimization:**

-   Reduce number of rows per file
-   Remove complex Excel formulas
-   Use plain data only
-   Consider background job for large datasets

## Future Enhancements

Possible improvements:

-   [ ] Async import dengan queue
-   [ ] Progress bar dengan percentage
-   [ ] Import preview before commit
-   [ ] Custom field mapping
-   [ ] Import history/log
-   [ ] Rollback last import
-   [ ] Validate file before upload
-   [ ] Support CSV format
-   [ ] Bulk update existing data
-   [ ] Email notification on completion

## Notes

-   Default password untuk akun user: `password123`
-   User diminta ganti password saat first login
-   Data yang error di-skip, tidak rollback seluruh import
-   Department dan Position harus sudah exist di database
-   Email harus unique per user
-   Kode karyawan harus unique
