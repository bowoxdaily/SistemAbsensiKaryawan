@extends('layouts.app')

@section('title', 'Backup & Restore Database')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Sistem /</span> Backup & Restore Database
        </h4>

        <!-- Alert Info -->
        <div class="alert alert-warning alert-dismissible shadow-none border" role="alert">
            <h6 class="alert-heading mb-2">
                <i class='bx bx-error-circle me-2'></i>Peringatan Penting
            </h6>
            <ul class="mb-0">
                <li>Backup database secara berkala untuk menghindari kehilangan data</li>
                <li>Restore akan menimpa semua data yang ada di database saat ini</li>
                <li>Pastikan backup dalam kondisi baik sebelum melakukan restore</li>
                <li>Disarankan download backup sebelum melakukan restore</li>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Mail Configuration Alert -->
        @if (config('mail.default') === 'log')
            <div class="alert alert-info alert-dismissible shadow-none border" role="alert">
                <h6 class="alert-heading mb-2">
                    <i class='bx bx-info-circle me-2'></i>Konfigurasi Email Diperlukan
                </h6>
                <p class="mb-2">Email saat ini menggunakan mode <code>log</code> (development). Email tidak akan
                    benar-benar terkirim.</p>
                <p class="mb-2"><strong>Untuk mengaktifkan pengiriman email, update file .env:</strong></p>
                <div class="bg-dark text-light p-3 rounded mb-2" style="font-family: monospace; font-size: 12px;">
                    <strong># Untuk Gmail:</strong><br>
                    MAIL_MAILER=smtp<br>
                    MAIL_HOST=smtp.gmail.com<br>
                    MAIL_PORT=587<br>
                    MAIL_USERNAME=email-anda@gmail.com<br>
                    MAIL_PASSWORD=your-app-password<br>
                    MAIL_ENCRYPTION=tls<br>
                    MAIL_FROM_ADDRESS=email-anda@gmail.com<br><br>

                    <strong># Atau untuk Mailtrap (Testing):</strong><br>
                    MAIL_MAILER=smtp<br>
                    MAIL_HOST=sandbox.smtp.mailtrap.io<br>
                    MAIL_PORT=2525<br>
                    MAIL_USERNAME=your-username<br>
                    MAIL_PASSWORD=your-password
                </div>
                <small>
                    <i class='bx bx-link-external me-1'></i>
                    <a href="https://mailtrap.io" target="_blank">Mailtrap</a> (gratis untuk testing) |
                    <a href="https://support.google.com/accounts/answer/185833" target="_blank">Gmail App Password</a>
                </small>
                <br>
                <small class="text-muted">Setelah update .env, jalankan: <code>php artisan config:cache</code></small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Backup Actions -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-down-arrow-circle me-2'></i>Aksi Backup
                        </h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary w-100 mb-3" onclick="createBackup()">
                            <i class='bx bx-save me-2'></i>Buat Backup Baru
                        </button>
                        <button class="btn btn-outline-secondary w-100" data-bs-toggle="modal"
                            data-bs-target="#uploadModal">
                            <i class='bx bx-upload me-2'></i>Upload Backup
                        </button>

                        <hr class="my-4">

                        <div class="mb-3">
                            <h6 class="mb-2">Informasi Database</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Database</th>
                                    <td>{{ config('database.connections.mysql.database') }}</td>
                                </tr>
                                <tr>
                                    <th>Host</th>
                                    <td>{{ config('database.connections.mysql.host') }}</td>
                                </tr>
                                <tr>
                                    <th>Driver</th>
                                    <td>{{ config('database.default') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="alert alert-info mb-0">
                            <small>
                                <i class='bx bx-info-circle me-1'></i>
                                Backup akan disimpan di <code>storage/app/backups/</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup List -->
            <div class="col-lg-8">
                <!-- Email Backup Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-envelope me-2'></i>Backup Email Otomatis
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="emailSettingsForm" onsubmit="saveEmailSettings(event)">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="backupEmail" class="form-label">Email Penerima Backup</label>
                                        <input type="email" class="form-control" id="backupEmail" name="backup_email"
                                            placeholder="admin@example.com" required>
                                        <div class="form-text">Backup akan dikirim ke email ini setiap 7 hari sekali
                                            (Minggu, 03:00 WIB)</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="emailEnabled"
                                                name="backup_email_enabled">
                                            <label class="form-check-label" for="emailEnabled">
                                                Aktifkan Email Backup
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="saveEmailBtn">
                                    <i class='bx bx-save me-1'></i>Simpan Pengaturan
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="sendTestEmail()">
                                    <i class='bx bx-paper-plane me-1'></i>Kirim Test Email
                                </button>
                            </div>
                        </form>

                        <div class="alert alert-warning mt-3 mb-0">
                            <small>
                                <i class='bx bx-info-circle me-1'></i>
                                <strong>Catatan:</strong> File backup yang lebih besar dari 25MB tidak akan dilampirkan di
                                email,
                                tetapi informasi backup tetap dikirim. Pastikan konfigurasi mail sudah benar di file .env
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class='bx bx-data me-2'></i>Daftar Backup
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="loadBackups()">
                            <i class='bx bx-refresh'></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="backupTable">
                                <thead>
                                    <tr>
                                        <th>Nama File</th>
                                        <th>Ukuran</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="backupTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <span class="ms-2">Memuat data...</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="uploadForm" onsubmit="uploadBackup(event)">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="backupFile" class="form-label">Pilih File Backup (.sql)</label>
                            <input type="file" class="form-control" id="backupFile" name="backup_file"
                                accept=".sql" required>
                            <div class="form-text">Maksimal ukuran file: 100MB</div>
                        </div>
                        <div class="alert alert-warning mb-0">
                            <small>
                                <i class='bx bx-error-circle me-1'></i>
                                Pastikan file backup berasal dari database yang sama dan dalam format SQL yang valid.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="uploadBtn">
                            <i class='bx bx-upload me-1'></i>Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Wait for all deferred scripts to load
        function initBackupPage() {
            console.log('=== SCRIPT LOADED ===');
            console.log('jQuery loaded:', typeof $ !== 'undefined');
            console.log('SweetAlert loaded:', typeof Swal !== 'undefined');
            console.log('Toastr loaded:', typeof toastr !== 'undefined');

            // If dependencies not ready, wait a bit
            if (typeof Swal === 'undefined' || typeof toastr === 'undefined') {
                console.log('Waiting for dependencies...');
                setTimeout(initBackupPage, 100);
                return;
            }

            console.log('=== DOM READY - All dependencies loaded ===');
            console.log('Page URL:', window.location.pathname);
            loadBackups();
            loadEmailSettings();
        }

        // Use both DOMContentLoaded and window.onload to ensure everything is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initBackupPage);
        } else {
            // DOM already loaded, run immediately
            initBackupPage();
        }

        function loadEmailSettings() {
            $.ajax({
                url: '/api/backup/email-settings',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#backupEmail').val(response.data.backup_email || '');
                        $('#emailEnabled').prop('checked', response.data.backup_email_enabled);
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load email settings', xhr);
                }
            });
        }

        function saveEmailSettings(event) {
            event.preventDefault();

            const saveBtn = $('#saveEmailBtn');
            saveBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Menyimpan...');

            const formData = {
                backup_email: $('#backupEmail').val(),
                backup_email_enabled: $('#emailEnabled').is(':checked')
            };

            $.ajax({
                url: '/api/backup/email-settings',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success(response.message);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan pengaturan');
                },
                complete: function() {
                    saveBtn.prop('disabled', false).html('<i class="bx bx-save me-1"></i>Simpan Pengaturan');
                }
            });
        }

        function sendTestEmail() {
            const email = $('#backupEmail').val();

            if (!email) {
                toastr.error('Silakan isi email terlebih dahulu');
                return;
            }

            Swal.fire({
                title: 'Kirim Test Email?',
                html: `Email akan dikirim ke: <strong>${email}</strong><br><small>Backup kecil akan dibuat dan dikirim sebagai test</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengirim Email...',
                        html: 'Mohon tunggu, proses sedang berjalan',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '/api/backup/send-test-email',
                        method: 'POST',
                        data: {
                            email: email
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Email Terkirim!',
                                text: response.message,
                                confirmButtonColor: '#696cff'
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Mengirim Email',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                                confirmButtonColor: '#696cff'
                            });
                        }
                    });
                }
            });
        }

        function loadBackups() {
            const tbody = $('#backupTableBody');
            tbody.html(`
                <tr>
                    <td colspan="4" class="text-center">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Memuat data backup...</span>
                    </td>
                </tr>
            `);

            $.ajax({
                url: '/api/backup/list',
                method: 'GET',
                timeout: 30000,
                success: function(response) {
                    tbody.empty();

                    if (response.success && response.backups && response.backups.length > 0) {
                        response.backups.forEach(function(backup) {
                            const isCorrupt = backup.is_corrupt || false;
                            const statusBadge = isCorrupt ?
                                '<span class="badge bg-danger ms-2">Corrupt</span>' :
                                '<span class="badge bg-success ms-2">Valid</span>';

                            const row = `
                                <tr class="${isCorrupt ? 'table-warning' : ''}">
                                    <td>
                                        <i class='bx ${isCorrupt ? 'bx-error' : 'bx-file'} me-2'></i>
                                        <span class="text-truncate" style="max-width: 200px;" title="${backup.filename}">
                                            ${backup.filename}
                                        </span>
                                        ${statusBadge}
                                    </td>
                                    <td>${backup.size_mb} MB</td>
                                    <td>${formatDate(backup.created_at)}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="downloadBackup('${backup.filename}')"
                                                title="Download">
                                                <i class='bx bx-download'></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success ${isCorrupt ? 'disabled' : ''}"
                                                onclick="${isCorrupt ? 'void(0)' : `confirmRestore('${backup.filename}')`}"
                                                title="${isCorrupt ? 'File corrupt, tidak bisa di-restore' : 'Restore'}">
                                                <i class='bx bx-revision'></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="confirmDelete('${backup.filename}')"
                                                title="Hapus">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    } else {
                        tbody.html(`
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class='bx bx-data bx-lg'></i>
                                    <p class="mb-0 mt-2">Belum ada backup tersedia</p>
                                    <small class="text-muted">Buat backup pertama untuk melindungi data Anda</small>
                                </td>
                            </tr>
                        `);
                    }
                },
                error: function(xhr) {
                    tbody.html(`
                        <tr>
                            <td colspan="4" class="text-center text-danger py-4">
                                <i class='bx bx-error-circle bx-lg'></i>
                                <p class="mb-0 mt-2">Gagal memuat daftar backup</p>
                                <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadBackups()">
                                    <i class='bx bx-refresh me-1'></i>Coba Lagi
                                </button>
                            </td>
                        </tr>
                    `);
                    console.error('Failed to load backups:', xhr);
                }
            });
        }

        function createBackup() {
            Swal.fire({
                title: 'Buat Backup Baru?',
                html: `
                    <p>Proses backup akan dimulai dan menyimpan data saat ini</p>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="includeUploads">
                        <label class="form-check-label" for="includeUploads">
                            Sertakan file uploads (foto, dokumen)
                        </label>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Buat Backup',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3',
                preConfirm: () => {
                    return {
                        include_uploads: document.getElementById('includeUploads').checked
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    performBackup(result.value);
                }
            });
        }

        function performBackup(options = {}) {
            Swal.fire({
                title: 'Membuat Backup...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Mohon tunggu, proses backup sedang berjalan...</p>
                        <div class="progress mt-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%" id="backupProgress"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Jangan tutup halaman ini</small>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Progress simulation
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress > 85) progress = 85;
                $('#backupProgress').css('width', progress + '%');
            }, 300);

            $.ajax({
                url: '/api/backup/create',
                method: 'POST',
                timeout: 180000, // 3 minutes timeout
                data: options,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    clearInterval(progressInterval);
                    $('#backupProgress').css('width', '100%');

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Backup Berhasil!',
                            html: `
                                <div class="text-center">
                                    <p class="mb-2">${response.message}</p>
                                    ${response.data?.filename ? `<code class="d-block mt-2 p-2 bg-light rounded">${response.data.filename}</code>` : ''}
                                    ${response.data?.size ? `<small class="text-muted">Ukuran: ${response.data.size}</small><br>` : ''}
                                    ${response.data?.tables_count ? `<small class="text-muted">Tables: ${response.data.tables_count}</small>` : ''}
                                </div>
                            `,
                            confirmButtonColor: '#696cff',
                            showCancelButton: true,
                            confirmButtonText: 'Lihat Daftar Backup',
                            cancelButtonText: 'Tutup'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                loadBackups();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Backup Gagal!',
                            text: response.message || 'Terjadi kesalahan saat membuat backup',
                            confirmButtonColor: '#696cff'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    clearInterval(progressInterval);

                    let errorMessage = 'Terjadi kesalahan saat membuat backup';
                    if (status === 'timeout') {
                        errorMessage = 'Timeout: Proses backup memakan waktu terlalu lama';
                    } else if (xhr.responseJSON?.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Backup Gagal!',
                        html: `
                            <p>${errorMessage}</p>
                            <small class="text-muted">Pastikan database dapat diakses dan ruang penyimpanan mencukupi</small>
                        `,
                        confirmButtonColor: '#696cff'
                    });
                }
            });
        }

        function confirmRestore(filename) {
            Swal.fire({
                title: 'Restore Database?',
                html: `
                    <div class="text-center">
                        <p class="mb-3">File: <code>${filename}</code></p>
                        <div class="alert alert-warning text-start">
                            <i class="bx bx-error-circle me-2"></i>
                            <strong>PERINGATAN:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Semua data saat ini akan digantikan dengan data dari backup</li>
                                <li>Proses ini tidak dapat dibatalkan</li>
                                <li>Pastikan backup dalam kondisi baik</li>
                            </ul>
                        </div>
                        <p class="text-muted small">Proses restore mungkin memakan waktu beberapa menit</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Restore Database',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d9534f',
                cancelButtonColor: '#8592a3',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    restoreBackup(filename);
                }
            });
        }

        function restoreBackup(filename) {
            console.log('=== RESTORE STARTED ===');
            console.log('Filename:', filename);

            Swal.fire({
                title: 'Restoring Database...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Mohon tunggu, proses restore sedang berjalan...</p>
                        <div class="progress mt-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%" id="restoreProgress"></div>
                        </div>
                        <small class="text-muted mt-2 d-block"><strong>JANGAN TUTUP HALAMAN INI!</strong></small>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Simulate progress for user feedback
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                $('#restoreProgress').css('width', progress + '%');
            }, 500);

            $.ajax({
                url: `/api/backup/restore/${filename}`,
                method: 'POST',
                timeout: 300000, // 5 minutes timeout
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    clearInterval(progressInterval);
                    $('#restoreProgress').css('width', '100%');

                    console.log('=== RESTORE SUCCESS ===');
                    console.log('Response:', response);

                    // Enhanced validation logic
                    const isSuccess = response.success === true;
                    const isRestored = response.restored === true || response.data?.restored === true;
                    const hasValidData = response.data && Object.keys(response.data).length > 0;

                    if (isSuccess && (isRestored || hasValidData)) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Restore Berhasil!',
                            html: `
                                <div class="text-center">
                                    <p class="mb-2">${response.message}</p>
                                    ${response.data?.tables_restored ? `<small class="text-muted">Tables restored: ${response.data.tables_restored}</small><br>` : ''}
                                    ${response.data?.records_count ? `<small class="text-muted">Records: ${response.data.records_count}</small><br>` : ''}
                                    <p class="text-muted small mt-3">Halaman akan reload dalam 3 detik untuk menampilkan data terbaru</p>
                                </div>
                            `,
                            confirmButtonColor: '#696cff',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: true,
                            confirmButtonText: 'Reload Sekarang'
                        }).then(() => {
                            location.reload();
                        });
                    } else if (isSuccess && !isRestored) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Restore Tidak Lengkap',
                            html: `
                                <div class="text-center">
                                    <p>File backup berhasil diproses, namun ada masalah dengan data:</p>
                                    <ul class="text-start mt-3">
                                        <li>File backup mungkin kosong atau corrupt</li>
                                        <li>Format SQL tidak kompatibel</li>
                                        <li>Constraint database error</li>
                                    </ul>
                                    <p class="text-muted small mt-3">Silakan cek file backup atau hubungi administrator</p>
                                </div>
                            `,
                            confirmButtonColor: '#696cff',
                            showCancelButton: true,
                            confirmButtonText: 'Coba Lagi',
                            cancelButtonText: 'Tutup'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                confirmRestore(filename);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Restore Gagal!',
                            html: `
                                <div class="text-center">
                                    <p>${response.message || 'Terjadi kesalahan saat restore database'}</p>
                                    <details class="text-start mt-3">
                                        <summary class="text-muted small">Detail Error (klik untuk expand)</summary>
                                        <pre class="text-muted small mt-2">${JSON.stringify(response, null, 2)}</pre>
                                    </details>
                                </div>
                            `,
                            confirmButtonColor: '#696cff'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    clearInterval(progressInterval);
                    console.error('=== RESTORE ERROR ===');
                    console.error('Status:', xhr.status, 'Error:', error);
                    console.error('Response:', xhr.responseJSON);

                    let errorMessage = 'Terjadi kesalahan saat restore database';
                    let errorDetails = error;

                    if (xhr.status === 504 || status === 'timeout') {
                        errorMessage = 'Timeout: Proses restore memakan waktu terlalu lama';
                        errorDetails =
                            'File backup mungkin terlalu besar. Coba gunakan file backup yang lebih kecil atau restore melalui command line.';
                    } else if (xhr.status === 413) {
                        errorMessage = 'File terlalu besar untuk diproses';
                        errorDetails = 'Ukuran file backup melebihi batas maksimal server.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error saat memproses backup';
                        errorDetails = xhr.responseJSON?.message || 'Internal server error';
                    } else if (xhr.responseJSON?.message) {
                        errorMessage = xhr.responseJSON.message;
                        errorDetails = xhr.responseJSON.error || 'Lihat console browser (F12) untuk detail';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Restore Gagal!',
                        html: `
                            <div class="text-center">
                                <p class="mb-3">${errorMessage}</p>
                                <div class="alert alert-danger text-start">
                                    <small><strong>Detail:</strong> ${errorDetails}</small>
                                </div>
                                <p class="text-muted small">Jika masalah berlanjut, hubungi administrator sistem</p>
                            </div>
                        `,
                        confirmButtonColor: '#696cff',
                        footer: `<small class="text-muted">Error Code: ${xhr.status} | ${new Date().toLocaleString()}</small>`
                    });
                },
                complete: function(xhr, status) {
                    clearInterval(progressInterval);
                    console.log('=== RESTORE COMPLETE ===');
                    console.log('Status:', status, 'HTTP Code:', xhr.status);
                }
            });
        }

        function downloadBackup(filename) {
            // Create a temporary link to trigger download
            const link = document.createElement('a');
            link.href = `/api/backup/download/${filename}`;
            link.download = filename;
            link.style.display = 'none';

            // Add to DOM, click, and remove
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Show success message
            toastr.success('Download dimulai...');
        }

        function confirmDelete(filename) {
            Swal.fire({
                title: 'Hapus Backup?',
                html: `File: <code>${filename}</code>`,
                text: 'File backup akan dihapus permanen',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d9534f',
                cancelButtonColor: '#8592a3'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteBackup(filename);
                }
            });
        }

        function deleteBackup(filename) {
            $.ajax({
                url: `/api/backup/delete/${filename}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success(response.message);
                    loadBackups();
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal menghapus backup');
                }
            });
        }

        function uploadBackup(event) {
            event.preventDefault();

            const fileInput = $('#backupFile')[0];
            const file = fileInput.files[0];

            if (!file) {
                toastr.error('Silakan pilih file backup terlebih dahulu');
                return;
            }

            // File validation
            if (!file.name.toLowerCase().endsWith('.sql')) {
                toastr.error('File harus berformat .sql');
                return;
            }

            if (file.size > 100 * 1024 * 1024) { // 100MB
                toastr.error('Ukuran file maksimal 100MB');
                return;
            }

            const formData = new FormData($('#uploadForm')[0]);
            const uploadBtn = $('#uploadBtn');

            uploadBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Uploading...');

            $.ajax({
                url: '/api/backup/upload',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                timeout: 180000, // 3 minutes
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    // Upload progress
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = evt.loaded / evt.total * 100;
                            uploadBtn.html(
                                `<i class="bx bx-loader-alt bx-spin me-1"></i>Uploading... ${Math.round(percentComplete)}%`
                            );
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#uploadModal').modal('hide');
                        $('#uploadForm')[0].reset();
                        loadBackups();
                    } else {
                        toastr.error(response.message || 'Gagal upload backup');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Gagal upload backup';
                    if (xhr.status === 413) {
                        errorMessage = 'File terlalu besar untuk di-upload';
                    } else if (xhr.responseJSON?.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    toastr.error(errorMessage);
                },
                complete: function() {
                    uploadBtn.prop('disabled', false).html('<i class="bx bx-upload me-1"></i>Upload');
                }
            });
        }

        function formatDate(timestamp) {
            const date = new Date(timestamp * 1000);
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return date.toLocaleDateString('id-ID', options);
        }
    </script>
@endpush
