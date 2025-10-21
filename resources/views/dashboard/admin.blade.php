@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="row">
        <!-- Welcome Card -->
        <div class="col-lg-8 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Selamat Datang Admin! 👋</h5>
                            <p class="mb-4">
                                Hari ini adalah <span
                                    class="fw-bold">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>.
                                Kelola sistem absensi karyawan dengan mudah.
                            </p>
                            <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua
                                Absensi</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('sneat-1.0.0/assets/img/illustrations/man-with-laptop-light.png') }}"
                                height="140" alt="Admin Dashboard" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Hari Ini -->
        <div class="col-lg-4 col-md-4 order-1">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <i class="bx bx-user-check bx-md text-success"></i>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Hadir Hari Ini</span>
                            <h3 class="card-title mb-2">{{ $hadirHariIni }}</h3>
                            <small class="text-success fw-semibold">Karyawan</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <i class="bx bx-user-x bx-md text-danger"></i>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Tidak Hadir</span>
                            <h3 class="card-title mb-2">{{ $tidakHadirHariIni }}</h3>
                            <small class="text-danger fw-semibold">Karyawan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Statistik Cards -->
        <div class="col-12 col-md-8 col-lg-4 order-3 order-md-2">
            <div class="row">
                <div class="col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <i class="bx bx-group bx-md text-primary"></i>
                                </div>
                            </div>
                            <span>Total Karyawan</span>
                            <h3 class="card-title text-nowrap mb-1">{{ $totalKaryawan }}</h3>
                            <small class="text-success fw-semibold">Aktif</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <i class="bx bx-building bx-md text-info"></i>
                                </div>
                            </div>
                            <span>Departemen</span>
                            <h3 class="card-title text-nowrap mb-1">{{ $totalDepartemen }}</h3>
                            <small class="text-muted fw-semibold">Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <i class="bx bx-calendar-event bx-md text-warning"></i>
                                </div>
                            </div>
                            <span>Pengajuan Cuti Pending</span>
                            <h3 class="card-title text-nowrap mb-1">{{ $totalCutiPending }}</h3>
                            <small class="text-warning fw-semibold">Menunggu Approval</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Absensi Terbaru -->
        <div class="col-12 col-md-8 col-lg-8 order-2 order-md-3 order-lg-2 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Absensi Terbaru Hari Ini</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Departemen</th>
                                    <th>Jabatan</th>
                                    <th>Jam Masuk</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($absensiTerbaru as $absensi)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <img src="{{ $absensi->employee->profile_photo ?? asset('sneat-1.0.0/assets/img/avatars/1.png') }}"
                                                        alt="Avatar" class="rounded-circle">
                                                </div>
                                                <div>
                                                    <strong>{{ $absensi->employee->name }}</strong><br>
                                                    <small
                                                        class="text-muted">{{ $absensi->employee->employee_code }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $absensi->employee->department->name ?? '-' }}</td>
                                        <td>{{ $absensi->employee->position->name ?? '-' }}</td>
                                        <td>{{ $absensi->check_in ? \Carbon\Carbon::parse($absensi->check_in)->format('H:i') : '-' }}
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
                                            @else
                                                <span class="badge bg-label-danger">{{ ucfirst($absensi->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data absensi hari ini</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengajuan Cuti Pending -->
    <div class="row">
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0">Pengajuan Cuti Pending</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @forelse($cutiPending as $cuti)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="mb-1">{{ $cuti->employee->name }}</h6>
                                <small class="text-muted">{{ $cuti->leave_type }} - {{ $cuti->total_days }}
                                    hari</small><br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($cuti->start_date)->format('d M Y') }} -
                                    {{ \Carbon\Carbon::parse($cuti->end_date)->format('d M Y') }}</small>
                            </div>
                            <div>
                                <span class="badge bg-label-warning">Pending</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted">Tidak ada pengajuan cuti pending</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Grafik Statistik Mingguan -->
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistik Absensi 7 Hari Terakhir</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartAbsensiAdmin" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart Statistik Mingguan
        const ctx = document.getElementById('chartAbsensiAdmin');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($statistikMingguIni['labels'] ?? []) !!},
                    datasets: [{
                        label: 'Hadir',
                        data: {!! json_encode($statistikMingguIni['hadir'] ?? []) !!},
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Tidak Hadir',
                        data: {!! json_encode($statistikMingguIni['tidak_hadir'] ?? []) !!},
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4
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
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    </script>
@endpush
