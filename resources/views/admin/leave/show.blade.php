@extends('layouts.app')

@section('title', 'Detail Pengajuan Cuti')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Admin / Cuti & Izin /</span> Detail Pengajuan
            </h4>
            <a href="{{ route('admin.leave.index') }}" class="btn btn-sm btn-secondary">
                <i class='bx bx-arrow-back'></i> Kembali
            </a>
        </div>

        <div class="row">
            <!-- Employee Information -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        @if ($leave->employee->photo)
                            <img src="{{ asset('storage/' . $leave->employee->photo) }}" alt="Foto Karyawan"
                                class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded-circle bg-label-primary" style="font-size: 48px;">
                                    {{ strtoupper(substr($leave->employee->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif

                        <h5 class="mb-1">{{ $leave->employee->name }}</h5>
                        <p class="text-muted mb-0">{{ $leave->employee->employee_id }}</p>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Departemen:</span>
                            <strong>{{ $leave->employee->department->name ?? '-' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Posisi:</span>
                            <strong>{{ $leave->employee->position->name ?? '-' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Email:</span>
                            <strong>{{ $leave->employee->email ?? '-' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Telepon:</span>
                            <strong>{{ $leave->employee->phone ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Details -->
            <div class="col-lg-8 mb-4">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Detail Pengajuan Cuti</h5>
                        @if ($leave->status == 'pending')
                            <span class="badge bg-label-warning">
                                <i class='bx bx-time-five'></i> Menunggu Persetujuan
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
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small mb-1">Jenis Cuti/Izin</label>
                                <div>
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
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1">Durasi</label>
                                <div>
                                    <strong class="h5 mb-0">{{ $leave->total_days }}</strong> <span
                                        class="text-muted">hari</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small mb-1">Tanggal Mulai</label>
                                <div><strong>{{ \Carbon\Carbon::parse($leave->start_date)->format('d F Y') }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1">Tanggal Selesai</label>
                                <div><strong>{{ \Carbon\Carbon::parse($leave->end_date)->format('d F Y') }}</strong></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small mb-1">Alasan</label>
                            <div class="border rounded p-3 bg-light">
                                {{ $leave->reason }}
                            </div>
                        </div>

                        @if ($leave->attachment)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Lampiran</label>
                                <div>
                                    @php
                                        $extension = pathinfo($leave->attachment, PATHINFO_EXTENSION);
                                    @endphp
                                    @if (in_array($extension, ['jpg', 'jpeg', 'png']))
                                        <a href="{{ asset('storage/' . $leave->attachment) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $leave->attachment) }}" alt="Lampiran"
                                                class="img-fluid rounded border" style="max-width: 300px;">
                                        </a>
                                    @else
                                        <a href="{{ asset('storage/' . $leave->attachment) }}" target="_blank"
                                            class="btn btn-outline-primary">
                                            <i class='bx bx-file'></i> Download Lampiran ({{ strtoupper($extension) }})
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small mb-1">Tanggal Pengajuan</label>
                                <div><strong>{{ $leave->created_at->format('d F Y, H:i') }} WIB</strong></div>
                            </div>
                            @if ($leave->approved_by)
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Diproses Oleh</label>
                                    <div><strong>{{ $leave->approver->name ?? '-' }}</strong></div>
                                </div>
                            @endif
                        </div>

                        @if ($leave->approved_at)
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Tanggal Diproses</label>
                                    <div><strong>{{ \Carbon\Carbon::parse($leave->approved_at)->format('d F Y, H:i') }}
                                            WIB</strong></div>
                                </div>
                            </div>
                        @endif

                        @if ($leave->status == 'rejected' && $leave->rejection_reason)
                            <div class="alert alert-danger mt-3 mb-0">
                                <h6 class="alert-heading"><i class='bx bx-error-circle'></i> Alasan Penolakan</h6>
                                <p class="mb-0">{{ $leave->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                @if ($leave->status == 'pending')
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">Tindakan</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <form action="{{ route('admin.leave.approve', $leave->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menyetujui pengajuan cuti ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class='bx bx-check-circle'></i> Setujui Pengajuan
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">
                                    <i class='bx bx-x-circle'></i> Tolak Pengajuan
                                </button>

                                <form action="{{ route('admin.leave.destroy', $leave->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus pengajuan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class='bx bx-trash'></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
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
                            <div class="alert alert-warning">
                                <strong>Perhatian!</strong> Tindakan ini akan menolak pengajuan cuti dari
                                <strong>{{ $leave->employee->name }}</strong>.
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror" rows="4"
                                    required placeholder="Jelaskan alasan penolakan kepada karyawan...">{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">
                                <i class='bx bx-x-circle'></i> Tolak Pengajuan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
