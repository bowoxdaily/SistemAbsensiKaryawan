@extends('layouts.app')

@section('title', 'Manajemen Cuti & Izin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Admin /</span> Manajemen Cuti & Izin
            </h4>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold d-block mb-1">Menunggu Persetujuan</span>
                                <h3 class="card-title mb-0">{{ $stats['pending'] }}</h3>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class='bx bx-time-five bx-sm'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold d-block mb-1">Disetujui</span>
                                <h3 class="card-title mb-0">{{ $stats['approved'] }}</h3>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class='bx bx-check-circle bx-sm'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-sm-12 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold d-block mb-1">Ditolak</span>
                                <h3 class="card-title mb-0">{{ $stats['rejected'] }}</h3>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class='bx bx-x-circle bx-sm'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Pengajuan Cuti & Izin</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapse">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
            </div>

            <!-- Filter Form -->
            <div class="collapse {{ request()->hasAny(['status', 'leave_type', 'start_date', 'end_date']) ? 'show' : '' }}"
                id="filterCollapse">
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.leave.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                        Disetujui</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label">Jenis</label>
                                <select name="leave_type" class="form-select">
                                    <option value="">Semua Jenis</option>
                                    <option value="cuti" {{ request('leave_type') == 'cuti' ? 'selected' : '' }}>Cuti
                                    </option>
                                    <option value="izin" {{ request('leave_type') == 'izin' ? 'selected' : '' }}>Izin
                                    </option>
                                    <option value="sakit" {{ request('leave_type') == 'sakit' ? 'selected' : '' }}>Sakit
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control"
                                    value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-search'></i> Filter
                            </button>
                            <a href="{{ route('admin.leave.index') }}" class="btn btn-outline-secondary">
                                <i class='bx bx-reset'></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="table-responsive text-nowrap d-none d-md-block">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Diajukan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($leaves as $leave)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($leave->employee->photo)
                                            <img src="{{ asset('storage/' . $leave->employee->photo) }}" alt="Foto"
                                                class="rounded-circle me-2"
                                                style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ strtoupper(substr($leave->employee->name, 0, 1)) }}
                                                </span>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $leave->employee->name }}</strong><br>
                                            <small class="text-muted">{{ $leave->employee->department->name ?? '-' }} -
                                                {{ $leave->employee->position->name ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($leave->leave_type == 'cuti')
                                        <span class="badge bg-label-primary">
                                            <i class='bx bx-calendar'></i> Cuti
                                        </span>
                                    @elseif($leave->leave_type == 'izin')
                                        <span class="badge bg-label-info">
                                            <i class='bx bx-time-five'></i> Izin
                                        </span>
                                    @else
                                        <span class="badge bg-label-warning">
                                            <i class='bx bx-first-aid'></i> Sakit
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</small><br>
                                    <small class="text-muted">s/d
                                        {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $leave->total_days }}</strong> hari
                                </td>
                                <td>
                                    @if ($leave->status == 'pending')
                                        <span class="badge bg-label-warning">
                                            <i class='bx bx-time-five'></i> Pending
                                        </span>
                                    @elseif($leave->status == 'approved')
                                        <span class="badge bg-label-success">
                                            <i class='bx bx-check-circle'></i> Disetujui
                                        </span>
                                    @else
                                        <span class="badge bg-label-danger">
                                            <i class='bx bx-x-circle'></i> Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $leave->created_at->format('d M Y') }}</small><br>
                                    <small class="text-muted">{{ $leave->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.leave.show', $leave->id) }}">
                                                <i class="bx bx-show me-1"></i> Lihat Detail
                                            </a>
                                            @if ($leave->status == 'pending')
                                                <form action="{{ route('admin.leave.approve', $leave->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item"
                                                        onclick="return confirm('Yakin ingin menyetujui cuti ini?')">
                                                        <i class="bx bx-check me-1"></i> Setujui
                                                    </button>
                                                </form>
                                                <button class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#rejectModal{{ $leave->id }}">
                                                    <i class="bx bx-x me-1"></i> Tolak
                                                </button>
                                            @endif
                                            <form action="{{ route('admin.leave.destroy', $leave->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger"
                                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <form action="{{ route('admin.leave.reject', $leave->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tolak Pengajuan Cuti</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Karyawan:</strong> {{ $leave->employee->name }}</p>
                                                <p><strong>Jenis:</strong> {{ ucfirst($leave->leave_type) }}</p>
                                                <p><strong>Tanggal:</strong>
                                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} -
                                                    {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</p>

                                                <div class="mb-3">
                                                    <label class="form-label">Alasan Penolakan <span
                                                            class="text-danger">*</span></label>
                                                    <textarea name="rejection_reason" class="form-control" rows="3" required
                                                        placeholder="Jelaskan alasan penolakan..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class='bx bx-x'></i> Tolak Pengajuan
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class='bx bx-calendar-x' style="font-size: 48px; color: #ccc;"></i>
                                    <p class="text-muted mt-2 mb-0">Tidak ada data pengajuan cuti</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="d-md-none">
                @forelse($leaves as $leave)
                    <div class="card-body border-bottom">
                        <div class="d-flex align-items-start mb-3">
                            @if ($leave->employee->photo)
                                <img src="{{ asset('storage/' . $leave->employee->photo) }}" alt="Foto"
                                    class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        {{ strtoupper(substr($leave->employee->name, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $leave->employee->name }}</h6>
                                <small class="text-muted">{{ $leave->employee->department->name ?? '-' }}</small>
                            </div>
                            @if ($leave->status == 'pending')
                                <span class="badge bg-label-warning">Pending</span>
                            @elseif($leave->status == 'approved')
                                <span class="badge bg-label-success">Disetujui</span>
                            @else
                                <span class="badge bg-label-danger">Ditolak</span>
                            @endif
                        </div>

                        <div class="mb-2">
                            @if ($leave->leave_type == 'cuti')
                                <span class="badge bg-label-primary"><i class='bx bx-calendar'></i> Cuti</span>
                            @elseif($leave->leave_type == 'izin')
                                <span class="badge bg-label-info"><i class='bx bx-time-five'></i> Izin</span>
                            @else
                                <span class="badge bg-label-warning"><i class='bx bx-first-aid'></i> Sakit</span>
                            @endif
                            <strong>{{ $leave->total_days }} hari</strong>
                        </div>

                        <small class="text-muted d-block mb-2">
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                        </small>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.leave.show', $leave->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class='bx bx-show'></i> Detail
                            </a>
                            @if ($leave->status == 'pending')
                                <form action="{{ route('admin.leave.approve', $leave->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"
                                        onclick="return confirm('Yakin ingin menyetujui?')">
                                        <i class='bx bx-check'></i> Setujui
                                    </button>
                                </form>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal{{ $leave->id }}">
                                    <i class='bx bx-x'></i> Tolak
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="card-body text-center py-4">
                        <i class='bx bx-calendar-x' style="font-size: 48px; color: #ccc;"></i>
                        <p class="text-muted mt-2 mb-0">Tidak ada data pengajuan cuti</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($leaves->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $leaves->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
