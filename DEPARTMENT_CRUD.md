# CRUD Department - Structure Documentation

## Folder Structure

```
app/
  Http/
    Controllers/
      Admin/
        DepartmentController.php    # Controller untuk CRUD Department (Admin Only)

resources/
  views/
    admin/
      departments/
        index.blade.php             # List semua departemen + pagination
        create.blade.php            # Form tambah departemen baru
        edit.blade.php              # Form edit departemen
        show.blade.php              # Detail departemen + list karyawan
```

## Routes

```php
Route::prefix('admin')->group(function () {
    Route::resource('departemen', DepartmentController::class);
});
```

### Generated Routes:

-   `GET    /admin/departemen` → departemen.index (List)
-   `GET    /admin/departemen/create` → departemen.create (Form Tambah)
-   `POST   /admin/departemen` → departemen.store (Simpan)
-   `GET    /admin/departemen/{id}` → departemen.show (Detail)
-   `GET    /admin/departemen/{id}/edit` → departemen.edit (Form Edit)
-   `PUT    /admin/departemen/{id}` → departemen.update (Update)
-   `DELETE /admin/departemen/{id}` → departemen.destroy (Hapus)

## Features

### Index Page (`index.blade.php`)

✅ List semua departemen dengan pagination
✅ Menampilkan jumlah karyawan per departemen
✅ Search & filter ready
✅ Action buttons: Detail, Edit, Delete
✅ Success/Error alert messages

### Create Page (`create.blade.php`)

✅ Form tambah departemen baru
✅ Validation: nama unik & required
✅ Info card dengan tips
✅ Responsive layout

### Edit Page (`edit.blade.php`)

✅ Form edit departemen
✅ Menampilkan info departemen (created, updated, jumlah karyawan)
✅ Warning jika departemen memiliki karyawan

### Show Page (`show.blade.php`)

✅ Detail informasi departemen
✅ List semua karyawan di departemen tersebut
✅ Statistics card (aktif, nonaktif, jabatan berbeda)
✅ Quick action buttons (Edit, Delete, Kembali)

## Validation Rules

**Store:**

-   `name`: required, string, max 100 char, unique
-   `description`: nullable, string, max 500 char

**Update:**

-   `name`: required, string, max 100 char, unique (except current)
-   `description`: nullable, string, max 500 char

**Delete:**

-   Tidak bisa hapus jika masih ada karyawan di departemen tersebut

## Controller Methods

```php
index()     → Tampilkan list departemen dengan jumlah karyawan
create()    → Form tambah departemen
store()     → Simpan departemen baru + validation
show()      → Detail departemen + load employees & positions
edit()      → Form edit departemen
update()    → Update departemen + validation
destroy()   → Hapus departemen (jika tidak ada karyawan)
```

## Database Seeder

Data default departemen (dari DepartmentSeeder):

1. Human Resources
2. Information Technology
3. Finance & Accounting
4. Marketing
5. Operations
6. Sales

## Next Steps

✅ Department CRUD completed
⏳ Position CRUD (Jabatan)
⏳ Employee CRUD (Karyawan)
⏳ Attendance Management
⏳ Leave Management

## Access Control

**Admin:** Full access (CRUD)
**Manager:** View only (untuk melihat struktur organisasi)
**Karyawan:** No access

## Notes

-   Folder `admin/` digunakan untuk semua fitur master data
-   Menggunakan resource controller untuk standarisasi
-   Bootstrap pagination untuk list data
-   Sneat template untuk UI/UX consistency
