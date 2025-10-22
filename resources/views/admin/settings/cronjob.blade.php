@extends('layouts.app')

@section('title', 'Pengaturan Cron Job')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Pengaturan /</span> Cron Job
        </h4>

        <!-- Alert Info -->
        <div class="alert alert-info alert-dismissible" role="alert">
            <h6 class="alert-heading mb-2">
                <i class='bx bx-info-circle me-2'></i>Apa itu Cron Job?
            </h6>
            <p class="mb-0">Cron Job adalah tugas otomatis yang berjalan secara terjadwal di server. Aplikasi ini
                membutuhkan Cron Job untuk menjalankan tugas-tugas seperti generate absensi alpha otomatis setiap hari.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="row">
            <!-- Command Cron Job -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-code-alt me-2'></i>Command Cron Job
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Auto Detect Info -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Informasi Server</h6>
                                <button class="btn btn-sm btn-outline-primary" onclick="detectEnvironment()">
                                    <i class='bx bx-refresh me-1'></i>Refresh
                                </button>
                            </div>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="30%">OS</th>
                                    <td id="serverOS">{{ PHP_OS }}</td>
                                </tr>
                                <tr>
                                    <th>PHP Version</th>
                                    <td>{{ PHP_VERSION }}</td>
                                </tr>
                                <tr>
                                    <th>Laravel Version</th>
                                    <td>{{ app()->version() }}</td>
                                </tr>
                                <tr>
                                    <th>Base Path</th>
                                    <td><code>{{ base_path() }}</code></td>
                                </tr>
                                <tr>
                                    <th>PHP Path</th>
                                    <td><code id="phpPath">{{ PHP_BINARY }}</code></td>
                                </tr>
                            </table>
                        </div>

                        <hr>

                        <!-- Cron Command -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Command untuk Server (Linux/Unix)</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" id="cronCommandLinux"
                                    value="* * * * * cd {{ base_path() }} && {{ PHP_BINARY }} artisan schedule:run >> /dev/null 2>&1"
                                    readonly>
                                <button class="btn btn-outline-primary" type="button"
                                    onclick="copyToClipboard('cronCommandLinux')">
                                    <i class='bx bx-copy'></i> Copy
                                </button>
                            </div>
                            <small class="text-muted">Untuk server Linux/Unix (cPanel, Plesk, VPS Linux)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Command untuk Windows</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" id="cronCommandWindows"
                                    value='schtasks /create /tn "Laravel Scheduler" /tr "cd {{ base_path() }} && {{ PHP_BINARY }} artisan schedule:run" /sc minute /mo 1'
                                    readonly>
                                <button class="btn btn-outline-primary" type="button"
                                    onclick="copyToClipboard('cronCommandWindows')">
                                    <i class='bx bx-copy'></i> Copy
                                </button>
                            </div>
                            <small class="text-muted">Untuk Windows Server / Local Development</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Alternative - Direct Artisan Command</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" id="cronCommandDirect"
                                    value="{{ PHP_BINARY }} {{ base_path('artisan') }} schedule:run" readonly>
                                <button class="btn btn-outline-primary" type="button"
                                    onclick="copyToClipboard('cronCommandDirect')">
                                    <i class='bx bx-copy'></i> Copy
                                </button>
                            </div>
                            <small class="text-muted">Command langsung tanpa cd ke directory</small>
                        </div>
                    </div>
                </div>

                <!-- Scheduled Tasks List -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-time me-2'></i>Daftar Scheduled Tasks
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Command</th>
                                        <th>Schedule</th>
                                        <th>Deskripsi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>attendance:generate-absent</code></td>
                                        <td><span class="badge bg-label-primary">Daily at 23:59</span></td>
                                        <td>Generate absensi alpha untuk karyawan yang tidak hadir</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-success"
                                                onclick="testCommand('attendance:generate-absent')">
                                                <i class='bx bx-play'></i> Test
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Setup Instructions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-book-open me-2'></i>Panduan Setup
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Tabs -->
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#cpanel">cPanel</button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#plesk">Plesk</button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#vps">VPS/SSH</button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#windows">Windows</button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- cPanel -->
                            <div class="tab-pane fade show active" id="cpanel" role="tabpanel">
                                <h6 class="mb-3">Setup di cPanel</h6>
                                <ol>
                                    <li>Login ke cPanel hosting Anda</li>
                                    <li>Cari menu <strong>"Cron Jobs"</strong> di bagian Advanced</li>
                                    <li>Pilih interval <strong>"Common Settings: Once Per Minute (****)"</strong></li>
                                    <li>Copy paste command Linux di atas ke field "Command"</li>
                                    <li>Klik <strong>"Add New Cron Job"</strong></li>
                                    <li>Cron Job akan otomatis berjalan setiap menit</li>
                                </ol>
                                <div class="alert alert-warning">
                                    <strong>Catatan:</strong> Pastikan path PHP sudah benar. Jika error, hubungi hosting
                                    provider untuk path PHP yang tepat.
                                </div>
                            </div>

                            <!-- Plesk -->
                            <div class="tab-pane fade" id="plesk" role="tabpanel">
                                <h6 class="mb-3">Setup di Plesk</h6>
                                <ol>
                                    <li>Login ke Plesk Panel</li>
                                    <li>Pilih domain/website Anda</li>
                                    <li>Klik <strong>"Scheduled Tasks"</strong></li>
                                    <li>Klik <strong>"Add Task"</strong></li>
                                    <li>Task type: <strong>Run a command</strong></li>
                                    <li>Schedule: <strong>*/1 * * * *</strong> (every minute)</li>
                                    <li>Copy paste command Linux di atas</li>
                                    <li>Klik <strong>"OK"</strong></li>
                                </ol>
                            </div>

                            <!-- VPS/SSH -->
                            <div class="tab-pane fade" id="vps" role="tabpanel">
                                <h6 class="mb-3">Setup di VPS via SSH</h6>
                                <ol>
                                    <li>SSH ke server VPS Anda</li>
                                    <li>Jalankan command: <code>crontab -e</code></li>
                                    <li>Tekan <kbd>i</kbd> untuk masuk mode insert</li>
                                    <li>Copy paste command Linux di atas</li>
                                    <li>Tekan <kbd>Esc</kbd> kemudian ketik <code>:wq</code> dan Enter</li>
                                    <li>Verifikasi: <code>crontab -l</code></li>
                                </ol>
                                <div class="alert alert-info">
                                    <strong>Tips:</strong> Untuk log cron, ubah command menjadi:<br>
                                    <code>* * * * * cd {{ base_path() }} && {{ PHP_BINARY }} artisan schedule:run >>
                                        {{ base_path('storage/logs/cron.log') }} 2>&1</code>
                                </div>
                            </div>

                            <!-- Windows -->
                            <div class="tab-pane fade" id="windows" role="tabpanel">
                                <h6 class="mb-3">Setup di Windows (Development)</h6>
                                <ol>
                                    <li>Buka <strong>Task Scheduler</strong></li>
                                    <li>Klik <strong>"Create Basic Task"</strong></li>
                                    <li>Name: <strong>Laravel Scheduler</strong></li>
                                    <li>Trigger: <strong>Daily</strong>, repeat every 1 minute</li>
                                    <li>Action: <strong>Start a program</strong></li>
                                    <li>Program/script: <code>{{ PHP_BINARY }}</code></li>
                                    <li>Add arguments: <code>artisan schedule:run</code></li>
                                    <li>Start in: <code>{{ base_path() }}</code></li>
                                    <li>Finish & Test</li>
                                </ol>
                                <div class="alert alert-warning">
                                    <strong>Alternatif untuk Development:</strong><br>
                                    Jalankan command: <code>php artisan schedule:work</code> di terminal (untuk testing
                                    saja)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Cron -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-check-shield me-2'></i>Status Cron
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Last Run</label>
                            <div class="d-flex align-items-center">
                                <i class='bx bx-time-five me-2 text-primary'></i>
                                <span id="lastRun">-</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Next Run</label>
                            <div class="d-flex align-items-center">
                                <i class='bx bx-calendar-event me-2 text-success'></i>
                                <span id="nextRun">-</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cron Status</label>
                            <div id="cronStatus">
                                <span class="badge bg-label-warning">Belum Diketahui</span>
                            </div>
                        </div>
                        <button class="btn btn-primary w-100" onclick="checkCronStatus()">
                            <i class='bx bx-refresh me-1'></i>Check Status
                        </button>
                    </div>
                </div>

                <!-- Quick Test -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-test-tube me-2'></i>Quick Test
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Test menjalankan scheduler secara manual</p>
                        <button class="btn btn-outline-success w-100 mb-2" onclick="runScheduler()">
                            <i class='bx bx-play-circle me-1'></i>Run Scheduler Now
                        </button>
                        <button class="btn btn-outline-info w-100" onclick="viewScheduleList()">
                            <i class='bx bx-list-ul me-1'></i>View Schedule List
                        </button>
                    </div>
                </div>

                <!-- Help -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class='bx bx-help-circle me-2'></i>Bantuan
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-2">Troubleshooting</h6>
                        <ul class="small mb-3">
                            <li>Pastikan path PHP sudah benar</li>
                            <li>Periksa permission folder storage/logs</li>
                            <li>Cek apakah cron sudah aktif di server</li>
                            <li>Lihat log di storage/logs/laravel.log</li>
                        </ul>

                        <h6 class="mb-2">Kontak Support</h6>
                        <p class="small mb-0">Jika mengalami kesulitan, hubungi administrator sistem atau hosting provider
                            Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Schedule List -->
    <div class="modal fade" id="scheduleListModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre id="scheduleListContent" class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"></pre>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function copyToClipboard(elementId) {
                const input = document.getElementById(elementId);
                input.select();
                input.setSelectionRange(0, 99999);

                navigator.clipboard.writeText(input.value).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Copied!',
                        text: 'Command berhasil di-copy ke clipboard',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                });
            }

            function detectEnvironment() {
                // Auto detect sudah dilakukan di server side
                Swal.fire({
                    icon: 'info',
                    title: 'Environment Detected',
                    text: 'Server information telah di-refresh',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            }

            function testCommand(command) {
                Swal.fire({
                    title: 'Test Command',
                    text: `Menjalankan command: ${command}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Run',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Running...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '/admin/settings/cronjob/test',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                command: command
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    html: response.message + '<br><small>' + response.output +
                                        '</small>',
                                    confirmButtonText: 'OK'
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message || 'Gagal menjalankan command',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            }

            function runScheduler() {
                Swal.fire({
                    title: 'Run Scheduler',
                    text: 'Menjalankan scheduler sekarang?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Run',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Running...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '/admin/settings/cronjob/run',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonText: 'OK'
                                });
                                checkCronStatus();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message ||
                                        'Gagal menjalankan scheduler',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            }

            function viewScheduleList() {
                $.ajax({
                    url: '/admin/settings/cronjob/list',
                    method: 'GET',
                    success: function(response) {
                        $('#scheduleListContent').text(response.output);
                        new bootstrap.Modal(document.getElementById('scheduleListModal')).show();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Gagal mengambil schedule list',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }

            function checkCronStatus() {
                $.ajax({
                    url: '/admin/settings/cronjob/status',
                    method: 'GET',
                    success: function(response) {
                        $('#lastRun').text(response.last_run || 'Belum pernah');
                        $('#nextRun').text(response.next_run || '-');

                        let statusBadge = '';
                        if (response.is_running) {
                            statusBadge = '<span class="badge bg-success">Active</span>';
                        } else {
                            statusBadge = '<span class="badge bg-danger">Inactive</span>';
                        }
                        $('#cronStatus').html(statusBadge);

                        Swal.fire({
                            icon: response.is_running ? 'success' : 'warning',
                            title: response.is_running ? 'Cron Active' : 'Cron Inactive',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            }

            // Auto check status on load
            $(document).ready(function() {
                checkCronStatus();
            });
        </script>
    @endpush
@endsection
