# SweetAlert2 Implementation

## Overview

SweetAlert2 telah diimplementasikan untuk konfirmasi delete pada modul Departments dan Karyawan, menggantikan `confirm()` native JavaScript dengan dialog yang lebih modern dan user-friendly.

## CDN Added

```html
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

Location: `resources/views/layouts/app.blade.php`

## Implementation

### Delete Confirmation Dialog

-   **Title**: Nama aksi (Hapus Departemen/Karyawan)
-   **Message**: Konfirmasi dengan warning
-   **Icon**: Warning (⚠️)
-   **Buttons**:
    -   Confirm: Merah dengan ikon trash
    -   Cancel: Biru dengan ikon X
-   **Reverse buttons**: Cancel di kiri, Confirm di kanan

### Success Response

-   **Title**: Berhasil!
-   **Message**: Response message dari server
-   **Icon**: Success (✓)
-   **Auto close**: 2 detik
-   **No confirm button**: Otomatis tertutup

### Error Response

-   **Title**: Gagal!
-   **Message**: Error message dari server
-   **Icon**: Error (✗)
-   **Confirm button**: "OK"

## Files Modified

1. ✅ `resources/views/layouts/app.blade.php` - Added SweetAlert2 CDN
2. ✅ `resources/views/admin/departments/index.blade.php` - Updated `deleteDepartment()` function
3. ✅ `resources/views/admin/karyawan/index.blade.php` - Updated `deleteKaryawan()` function

## Features

-   ✅ Modern UI dengan animasi smooth
-   ✅ Responsive design
-   ✅ Customizable button text dan icon
-   ✅ HTML support dalam message
-   ✅ Auto-close untuk success message
-   ✅ Promise-based (menggunakan `.then()`)

## Usage Example

```javascript
Swal.fire({
    title: "Hapus Data?",
    html: 'Apakah Anda yakin?<br><small class="text-danger">Peringatan: Data akan terhapus permanen.</small>',
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: '<i class="bx bx-trash"></i> Ya, Hapus!',
    cancelButtonText: '<i class="bx bx-x"></i> Batal',
    buttonsStyling: true,
    reverseButtons: true,
}).then((result) => {
    if (result.isConfirmed) {
        // Proses delete
        Swal.fire({
            title: "Berhasil!",
            text: "Data berhasil dihapus",
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
        });
    }
});
```

## Benefits

-   Lebih menarik secara visual dibanding `confirm()` native
-   Konsisten dengan desain UI modern
-   Support HTML dan custom styling
-   Better UX dengan animasi dan feedback visual
-   Mobile-friendly

## Documentation

Official docs: https://sweetalert2.github.io/
