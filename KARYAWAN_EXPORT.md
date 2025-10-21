# Export Karyawan ke Excel - Dokumentasi

## Overview

Fitur untuk mengexport data karyawan ke file Excel (.xlsx) dengan format yang rapi dan terstruktur.

## Package Used

-   **Laravel Excel (maatwebsite/excel)** v3.1
-   PhpSpreadsheet untuk styling dan formatting

## Files Created/Modified

### 1. Export Class

**Path:** `app/Exports/KaryawanExport.php`

**Implements:**

-   `FromCollection` - Ambil data dari database
-   `WithHeadings` - Tambah header kolom
-   `WithMapping` - Format data setiap row
-   `WithStyles` - Styling header (bold, background color)
-   `WithColumnWidths` - Set lebar kolom otomatis

**Data yang Diexport:**

1. Kode Karyawan
2. NIK
3. Nama Lengkap
4. Jenis Kelamin
5. Tempat Lahir
6. Tanggal Lahir
7. Status Perkawinan
8. Departemen
9. Posisi
10. Tanggal Bergabung
11. Status Kerja
12. Jenis Shift
13. Status
14. Alamat
15. Kota
16. Provinsi
17. Kode Pos
18. No. HP
19. Email
20. Kontak Darurat (Nama)
21. Kontak Darurat (No)

### 2. Controller Method

**Path:** `app/Http/Controllers/Admin/KaryawanController.php`

**Method Added:**

```php
public function export()
{
    return Excel::download(
        new KaryawanExport,
        'Data_Karyawan_' . date('Y-m-d_His') . '.xlsx'
    );
}
```

**Imports Added:**

```php
use App\Exports\KaryawanExport;
use Maatwebsite\Excel\Facades\Excel;
```

### 3. Route

**Path:** `routes/web.php`

```php
Route::get('/admin/karyawan/export', [KaryawanController::class, 'export'])
    ->name('admin.karyawan.export');
```

### 4. View (Button)

**Path:** `resources/views/admin/karyawan/index.blade.php`

**Button Export:**

```html
<a href="{{ route('admin.karyawan.export') }}" class="btn btn-success btn-sm">
    <i class="bx bx-download me-1"></i>
    <span class="d-none d-sm-inline">Export Excel</span>
</a>
```

## Features

### 1. Auto Formatting

-   ✅ Header dengan background hijau (#E8F5E9)
-   ✅ Header text bold dan centered
-   ✅ Column width otomatis disesuaikan dengan content
-   ✅ Data terformat rapi dan mudah dibaca

### 2. Data Processing

-   ✅ Include relasi (Department, Position)
-   ✅ Format gender (L → Laki-laki, P → Perempuan)
-   ✅ Format status (active → Aktif, etc.)
-   ✅ Handle null values (tampilkan "-")
-   ✅ Sort by employee_code ascending

### 3. File Naming

Format: `Data_Karyawan_YYYY-MM-DD_HHmmss.xlsx`

Contoh: `Data_Karyawan_2025-10-21_143055.xlsx`

## Column Widths

| Column | Field                 | Width |
| ------ | --------------------- | ----- |
| A      | Kode Karyawan         | 15    |
| B      | NIK                   | 18    |
| C      | Nama Lengkap          | 25    |
| D      | Jenis Kelamin         | 15    |
| E      | Tempat Lahir          | 20    |
| F      | Tanggal Lahir         | 15    |
| G      | Status Perkawinan     | 18    |
| H      | Departemen            | 20    |
| I      | Posisi                | 20    |
| J      | Tanggal Bergabung     | 18    |
| K      | Status Kerja          | 15    |
| L      | Jenis Shift           | 12    |
| M      | Status                | 12    |
| N      | Alamat                | 35    |
| O      | Kota                  | 15    |
| P      | Provinsi              | 15    |
| Q      | Kode Pos              | 12    |
| R      | No. HP                | 15    |
| S      | Email                 | 25    |
| T      | Kontak Darurat (Nama) | 25    |
| U      | Kontak Darurat (No)   | 15    |

## Usage

### Export All Data

1. Login ke sistem
2. Buka halaman `/admin/karyawan`
3. Klik tombol **"Export Excel"** (hijau, icon download)
4. File akan otomatis terdownload ke browser

### File Output

-   Format: Excel 2007+ (.xlsx)
-   Encoding: UTF-8
-   Readable di: Microsoft Excel, Google Sheets, LibreOffice Calc

## Configuration

**File:** `config/excel.php`

Key configurations:

```php
'exports' => [
    'chunk_size' => 1000,
    'temp_path' => storage_path('framework/cache/laravel-excel'),
],
```

## Performance

### Optimization

-   ✅ Eager loading relasi (department, position) untuk avoid N+1 queries
-   ✅ Chunk processing untuk dataset besar
-   ✅ Memory efficient dengan streaming

### Recommended Limits

-   < 1,000 records: Instant download
-   1,000 - 10,000 records: 2-5 seconds
-   > 10,000 records: Consider background job

## Example Output

### Header Row (Green Background, Bold)

```
Kode Karyawan | NIK | Nama Lengkap | Jenis Kelamin | ...
```

### Data Rows

```
EMP001 | 1234567890123456 | John Doe | Laki-laki | Jakarta | ...
EMP002 | 9876543210987654 | Jane Smith | Perempuan | Bandung | ...
```

## Troubleshooting

### Error: "Class not found"

**Solution:**

```bash
composer dump-autoload
php artisan config:clear
```

### Error: "Permission denied"

**Solution:**
Pastikan folder `storage/framework/cache` writable:

```bash
chmod -R 775 storage/
```

### Excel file corrupted

**Solution:**

1. Clear cache: `php artisan cache:clear`
2. Republish config: `php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config --force`

## Future Enhancements

Possible improvements:

-   [ ] Filter export by department/position
-   [ ] Export by date range
-   [ ] Custom column selection
-   [ ] Export to PDF
-   [ ] Export to CSV
-   [ ] Schedule automatic export
-   [ ] Email export hasil to admin

## Testing

### Manual Test

1. Pastikan ada data karyawan
2. Click "Export Excel"
3. Verify file downloaded
4. Open di Excel/Google Sheets
5. Check formatting & data completeness

### Expected Result

✅ File ter-download dengan nama berformat timestamp
✅ Header hijau dan bold
✅ Data lengkap sesuai database
✅ Format tanggal readable
✅ Tidak ada error

## Notes

-   Export includes **ALL** active and inactive employees
-   Data di-sort berdasarkan employee_code ascending
-   Relasi (department, position) otomatis di-load
-   File disimpan temporary di `storage/framework/cache/laravel-excel`
-   File otomatis dihapus setelah download
