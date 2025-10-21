# Template Layout - Sistem Absensi Karyawan

Template admin yang sudah diintegrasikan dengan Laravel menggunakan Sneat Bootstrap 5 Admin Template.

## Struktur File Layout

```
resources/views/
├── layouts/
│   ├── app.blade.php              # Main layout untuk halaman authenticated
│   ├── auth.blade.php             # Layout untuk halaman authentication
│   └── partials/
│       ├── sidebar.blade.php      # Sidebar menu navigasi
│       ├── navbar.blade.php       # Top navbar dengan user info
│       └── footer.blade.php       # Footer
├── auth/
│   └── login.blade.php           # Halaman login
└── dashboard.blade.php            # Halaman dashboard utama
```

## Controller yang Tersedia

-   `DashboardController` - Controller untuk dashboard
-   `Auth\LoginController` - Controller untuk autentikasi

## Routes yang Tersedia

### Public Routes

-   `/` - Redirect ke login
-   `/login` - Halaman login

### Protected Routes (Memerlukan autentikasi)

-   `/dashboard` - Dashboard utama
-   `/master/karyawan` - CRUD Karyawan
-   `/master/departemen` - CRUD Departemen
-   `/master/jabatan` - CRUD Jabatan
-   `/absensi` - Halaman absensi harian
-   `/absensi/rekap` - Rekap absensi
-   `/settings/jam-kerja` - Pengaturan jam kerja
-   `/settings/users` - Manajemen user
-   `/profile` - Halaman profil user

## Menu Sidebar

1. **Dashboard** - Halaman utama dengan statistik
2. **Master Data**
    - Karyawan
    - Departemen
    - Jabatan
3. **Absensi**
    - Absensi Harian
    - Rekap Absensi
4. **Pengaturan**
    - Jam Kerja
    - Users

## Fitur Template

### Layout App (Main)

-   ✅ Responsive sidebar menu
-   ✅ Top navbar dengan search dan user dropdown
-   ✅ Real-time datetime display
-   ✅ User profile dengan avatar
-   ✅ Logout functionality
-   ✅ Footer dengan informasi copyright

### Dashboard

-   ✅ Welcome card
-   ✅ Statistik cards (Hadir, Tidak Hadir, Total Karyawan, Departemen)
-   ✅ Tabel absensi terbaru
-   ✅ Chart untuk statistik mingguan
-   ✅ Responsive layout

### Login Page

-   ✅ Clean authentication form
-   ✅ Password visibility toggle
-   ✅ Remember me checkbox
-   ✅ Error handling dengan alert
-   ✅ Responsive design

## Cara Penggunaan

### 1. Menggunakan Layout di View Baru

```blade
@extends('layouts.app')

@section('title', 'Judul Halaman')

@section('content')
    <!-- Konten halaman di sini -->
@endsection

@push('styles')
    <!-- CSS tambahan -->
@endpush

@push('scripts')
    <!-- JavaScript tambahan -->
@endpush
```

### 2. Menambah Menu di Sidebar

Edit file `resources/views/layouts/partials/sidebar.blade.php`:

```blade
<li class="menu-item {{ request()->routeIs('nama-route.*') ? 'active' : '' }}">
    <a href="{{ route('nama-route.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-icon"></i>
        <div data-i18n="Nama Menu">Nama Menu</div>
    </a>
</li>
```

### 3. Menambah Route

Edit file `routes/web.php`:

```php
Route::middleware(['auth'])->group(function () {
    Route::resource('nama-route', NamaController::class);
});
```

## Assets yang Digunakan

Template menggunakan assets dari folder `public/sneat-1.0.0/`:

-   CSS: Bootstrap 5, Theme default, Core styles
-   JavaScript: jQuery, Bootstrap, Perfect Scrollbar, Menu
-   Icons: Boxicons
-   Fonts: Public Sans

## Next Steps

Untuk melanjutkan development:

1. **Buat Database & Migrations**

    ```bash
    php artisan make:migration create_karyawan_table
    php artisan make:migration create_departemen_table
    php artisan make:migration create_absensi_table
    ```

2. **Buat Models**

    ```bash
    php artisan make:model Karyawan
    php artisan make:model Departemen
    php artisan make:model Absensi
    ```

3. **Buat Controllers yang belum ada**

    ```bash
    php artisan make:controller KaryawanController --resource
    php artisan make:controller DepartemenController --resource
    php artisan make:controller AbsensiController
    ```

4. **Setup Authentication**
    - User sudah ada di database migrations default Laravel
    - Buat seeder untuk user admin:
    ```bash
    php artisan make:seeder UserSeeder
    ```

## Customization

### Mengubah Warna Theme

Edit `public/sneat-1.0.0/assets/vendor/css/theme-default.css`

### Mengubah Logo

Ganti logo SVG di `resources/views/layouts/partials/sidebar.blade.php`

### Mengubah Informasi Footer

Edit `resources/views/layouts/partials/footer.blade.php`

## Dukungan Browser

-   Chrome (latest)
-   Firefox (latest)
-   Safari (latest)
-   Edge (latest)

## Lisensi

Template Sneat adalah produk premium. Pastikan Anda memiliki lisensi yang valid.
