@extends('layouts.app')

@section('title', 'Dashboard Karyawan')

@section('content')
    @if (isset($message))
        <div class="alert alert-warning">
            {{ $message }}
        </div>
    @else
        <div class="row">
            <!-- Welcome Card & Absen Hari Ini -->
            <div class="col-lg-8 mb-4 order-0">
                <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Selamat Datang, {{ Auth::user()->name }}! 👋</h5>
                                <p class="mb-2">
                                    <strong>NIK:</strong> {{ $employee->nik ?? '-' }}<br>
                                    <strong>Kode:</strong> {{ $employee->employee_code }}<br>
                                    <strong>Departemen:</strong> {{ $employee->department->name ?? '-' }}<br>
                                    <strong>Jabatan:</strong> {{ $employee->position->name ?? '-' }}
                                </p>

                                @if ($absensiHariIni)
                                    <div class="alert alert-success p-2 mb-2">
                                        <small><i class="bx bx-check-circle"></i> Anda sudah absen hari ini</small><br>
                                        <small><strong>Masuk:</strong>
                                            {{ \Carbon\Carbon::parse($absensiHariIni->check_in)->format('H:i') }}</small>
                                        @if ($absensiHariIni->check_out)
                                            <small class="ms-2"><strong>Keluar:</strong>
                                                {{ \Carbon\Carbon::parse($absensiHariIni->check_out)->format('H:i') }}</small>
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-warning p-2 mb-2">
                                        <small><i class="bx bx-time"></i> Anda belum absen hari ini</small>
                                    </div>
                                    <a href="{{ route('employee.attendance.index') }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-fingerprint"></i> Absen Sekarang
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <img src="{{ asset('sneat-1.0.0/assets/img/illustrations/man-with-laptop-light.png') }}"
                                    height="120" alt="Karyawan Dashboard" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Bulan Ini -->
            <div class="col-lg-4 col-md-4 order-1">
                <div class="row">
                    <div class="col-lg-6 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <i class="bx bx-calendar-check bx-md text-success"></i>
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">Hadir</span>
                                <h3 class="card-title mb-2">{{ $totalHadirBulanIni }}</h3>
                                <small class="text-success fw-semibold">Bulan Ini</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <i class="bx bx-time-five bx-md text-warning"></i>
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">Terlambat</span>
                                <h3 class="card-title mb-2">{{ $totalTerlambatBulanIni }}</h3>
                                <small class="text-warning fw-semibold">Bulan Ini</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Info Cuti -->
            <div class="col-12 col-md-8 col-lg-4 order-3 order-md-2">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <i class="bx bx-calendar-event bx-md text-info"></i>
                                    </div>
                                </div>
                                <span>Sisa Cuti Tahunan</span>
                                <h3 class="card-title text-nowrap mb-1">{{ $cutiTersedia - $cutiTerpakai }} Hari</h3>
                                <small class="text-muted fw-semibold">Dari {{ $cutiTersedia }} hari</small>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-info"
                                        style="width: {{ (($cutiTersedia - $cutiTerpakai) / $cutiTersedia) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <i class="bx bx-user-x bx-md text-danger"></i>
                                    </div>
                                </div>
                                <span>Izin/Sakit</span>
                                <h3 class="card-title text-nowrap mb-1">{{ $totalIzinBulanIni }} Hari</h3>
                                <small class="text-danger fw-semibold">Bulan Ini</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Absensi -->
            <div class="col-12 col-md-8 col-lg-8 order-2 order-md-3 order-lg-2 mb-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Riwayat Absensi Terbaru</h5>
                        <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @forelse($riwayatAbsensi as $absensi)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($absensi->attendance_date)->translatedFormat('d M Y') }}
                                            </td>
                                            <td>{{ $absensi->check_in ? \Carbon\Carbon::parse($absensi->check_in)->format('H:i') : '-' }}
                                            </td>
                                            <td>{{ $absensi->check_out ? \Carbon\Carbon::parse($absensi->check_out)->format('H:i') : '-' }}
                                            </td>
                                            <td>
                                                @if ($absensi->status == 'hadir')
                                                    <span class="badge bg-label-success">Hadir</span>
                                                @elseif($absensi->status == 'terlambat')
                                                    <span class="badge bg-label-warning">Terlambat</span>
                                                @elseif($absensi->status == 'izin')
                                                    <span class="badge bg-label-info">Izin</span>
                                                @elseif($absensi->status == 'sakit')
                                                    <span class="badge bg-label-secondary">Sakit</span>
                                                @elseif($absensi->status == 'cuti')
                                                    <span class="badge bg-label-primary">Cuti</span>
                                                @else
                                                    <span
                                                        class="badge bg-label-danger">{{ ucfirst($absensi->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($absensi->status == 'terlambat')
                                                    <small class="text-warning">{{ $absensi->late_minutes }} menit</small>
                                                @elseif($absensi->notes)
                                                    <small>{{ Str::limit($absensi->notes, 30) }}</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada riwayat absensi</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengajuan Cuti & Quick Actions -->
        <div class="row">
            <div class="col-md-6 col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0">Pengajuan Cuti Anda</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCuti">
                            <i class="bx bx-plus"></i> Ajukan Cuti
                        </button>
                    </div>
                    <div class="card-body">
                        @forelse($cutiPending as $cuti)
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <h6 class="mb-1">{{ ucfirst($cuti->leave_type) }} - {{ $cuti->total_days }} hari
                                    </h6>
                                    <small
                                        class="text-muted">{{ \Carbon\Carbon::parse($cuti->start_date)->format('d M Y') }}
                                        - {{ \Carbon\Carbon::parse($cuti->end_date)->format('d M Y') }}</small><br>
                                    <small><strong>Alasan:</strong> {{ Str::limit($cuti->reason, 50) }}</small>
                                </div>
                                <div>
                                    @if ($cuti->status == 'pending')
                                        <span class="badge bg-label-warning">Pending</span>
                                    @elseif($cuti->status == 'approved')
                                        <span class="badge bg-label-success">Disetujui</span>
                                    @else
                                        <span class="badge bg-label-danger">Ditolak</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted">Tidak ada pengajuan cuti</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Grafik Kehadiran Bulan Ini -->
            <div class="col-md-6 col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Statistik Kehadiran Bulan Ini</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartAbsensiKaryawan" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Absen -->
        <div class="modal fade" id="modalAbsen" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Absen Sekarang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="hadir">Hadir</option>
                                    <option value="izin">Izin</option>
                                    <option value="sakit">Sakit</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan (Opsional)</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto (Opsional)</label>
                                <input type="file" name="photo_in" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Absensi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Ajukan Cuti -->
        <div class="modal fade" id="modalCuti" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajukan Cuti/Izin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Jenis</label>
                                <select name="leave_type" class="form-control" required>
                                    <option value="cuti">Cuti</option>
                                    <option value="izin">Izin</option>
                                    <option value="sakit">Sakit</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="start_date" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alasan</label>
                                <textarea name="reason" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lampiran (Opsional)</label>
                                <input type="file" name="attachment" class="form-control">
                                <small class="text-muted">Upload surat keterangan dokter jika sakit</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Ajukan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart Statistik Bulan Ini
        const ctx = document.getElementById('chartAbsensiKaryawan');
        if (ctx) {
            const labels = {!! json_encode($statistikBulanIni['labels'] ?? []) !!};
            const statuses = {!! json_encode($statistikBulanIni['status'] ?? []) !!};

            // Convert status to numeric data
            const hadirData = statuses.map(s => ['hadir', 'terlambat'].includes(s) ? 1 : 0);
            const izinData = statuses.map(s => ['izin', 'sakit', 'cuti'].includes(s) ? 1 : 0);
            const alphaData = statuses.map(s => s === 'alpha' ? 1 : 0);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Hadir',
                        data: hadirData,
                        backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    }, {
                        label: 'Izin/Sakit',
                        data: izinData,
                        backgroundColor: 'rgba(255, 206, 86, 0.8)',
                    }, {
                        label: 'Alpha',
                        data: alphaData,
                        backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 1,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return value === 1 ? 'Ada' : 'Tidak';
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
@endpush
