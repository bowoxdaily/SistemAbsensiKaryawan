@extends('layouts.app')

@section('title', 'Riwayat Payroll')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">💰 Riwayat Payroll</h4>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4" id="statsCards">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class='bx bx-receipt bx-sm'></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Diterima</small>
                                <h5 class="mb-0" id="totalReceived">-</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class='bx bx-dollar bx-sm'></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Pendapatan Tahun Ini</small>
                                <h5 class="mb-0" id="totalEarningsYear">-</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class='bx bx-calendar bx-sm'></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Payroll Terakhir</small>
                                <h6 class="mb-0" id="latestPeriod">-</h6>
                                <small class="text-primary fw-bold" id="latestAmount">-</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Periode</label>
                        <input type="month" class="form-control" id="filterPeriod">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">Semua Status</option>
                            <option value="sent">Terkirim</option>
                            <option value="paid">Dibayar</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cari Kode</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari kode payroll...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-secondary w-100" id="btnResetFilter">
                            <i class='bx bx-reset'></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="card d-none d-md-block">
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Periode</th>
                            <th>Tgl Bayar</th>
                            <th class="text-end">Pendapatan</th>
                            <th class="text-end">Potongan</th>
                            <th class="text-end">Gaji Bersih</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="payrollTableBody">
                        <tr>
                            <td colspan="8" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div id="pagination"></div>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="d-md-none" id="mobileView"></div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Slip Gaji</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <div class="text-center py-5">
                        <i class="bx bx-loader bx-spin bx-lg"></i>
                        <p>Memuat detail...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            console.log('🚀 Employee Payroll History page initialized');
            let currentPage = 1;

            // Load initial data
            loadStatistics();
            loadPayrolls();

            // Event Listeners
            $(document).on('click', '#btnResetFilter', resetFilters);
            $(document).on('click', '.btn-view-detail', function() {
                let id = $(this).data('id');
                viewDetail(id);
            });
            $(document).on('click', '.pagination-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                loadPayrolls(page);
            });

            $('#searchInput').on('keyup', debounce(loadPayrolls, 500));
            $('#filterPeriod, #filterStatus').on('change', loadPayrolls);

            // Functions
            function loadStatistics() {
                console.log('📊 Loading statistics...');
                $.get('/api/employee/payroll/statistics')
                    .done(function(res) {
                        console.log('✅ Statistics loaded:', res);
                        if (res.success) {
                            let data = res.data;
                            $('#totalReceived').text(data.total_received + ' slip');
                            $('#totalEarningsYear').text(formatCurrency(data.total_earnings_this_year));

                            if (data.latest_payroll) {
                                $('#latestPeriod').text(formatPeriod(data.latest_payroll.period_month));
                                $('#latestAmount').text(formatCurrency(data.latest_payroll.net_salary));
                            } else {
                                $('#latestPeriod').text('Belum ada payroll');
                                $('#latestAmount').text('-');
                            }
                        }
                    })
                    .fail(function(xhr) {
                        console.error('❌ Error loading statistics:', xhr);
                    });
            }

            function loadPayrolls(page = 1) {
                console.log(`📋 Loading payrolls - Page ${page}`);
                currentPage = page;
                let params = {
                    page: page,
                    per_page: 10,
                    search: $('#searchInput').val(),
                    period: $('#filterPeriod').val(),
                    status: $('#filterStatus').val()
                };

                $('#payrollTableBody').html(
                    '<tr><td colspan="8" class="text-center"><i class="bx bx-loader bx-spin"></i> Memuat data...</td></tr>'
                );

                $.get('/api/employee/payroll', params)
                    .done(function(res) {
                        console.log('✅ Payrolls loaded:', res);
                        if (res.success) {
                            renderTable(res.data.data);
                            renderPagination(res.data);
                            renderMobileCards(res.data.data);
                        } else {
                            $('#payrollTableBody').html(
                                '<tr><td colspan="8" class="text-center text-warning">Tidak ada data</td></tr>'
                            );
                        }
                    })
                    .fail(function(xhr) {
                        console.error('❌ Error loading payrolls:', xhr);
                        $('#payrollTableBody').html(
                            '<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>'
                        );
                        toastr.error('Gagal memuat data payroll');
                    });
            }

            function renderTable(payrolls) {
                if (payrolls.length === 0) {
                    $('#payrollTableBody').html(
                        '<tr><td colspan="8" class="text-center">Belum ada riwayat payroll</td></tr>');
                    return;
                }

                let html = '';
                payrolls.forEach(function(p) {
                    let statusBadge = {
                        draft: '<span class="badge bg-secondary">Draft</span>',
                        sent: '<span class="badge bg-success">Terkirim</span>',
                        paid: '<span class="badge bg-primary">Dibayar</span>'
                    };

                    html += `<tr>
                        <td><div class="fw-bold">${p.payroll_code}</div></td>
                        <td>${formatPeriod(p.period_month)}</td>
                        <td>${formatDate(p.payment_date)}</td>
                        <td class="text-end text-success">${formatCurrency(p.total_earnings)}</td>
                        <td class="text-end text-danger">${formatCurrency(p.total_deductions)}</td>
                        <td class="text-end fw-bold text-primary">${formatCurrency(p.net_salary)}</td>
                        <td>${statusBadge[p.status]}</td>
                        <td>
                            <button class="btn btn-sm btn-info btn-view-detail" data-id="${p.id}" title="Lihat Detail">
                                <i class='bx bx-show'></i> Detail
                            </button>
                        </td>
                    </tr>`;
                });
                $('#payrollTableBody').html(html);
            }

            function renderMobileCards(payrolls) {
                if (payrolls.length === 0) {
                    $('#mobileView').html('<div class="alert alert-info">Belum ada riwayat payroll</div>');
                    return;
                }

                let html = '';
                payrolls.forEach(function(p) {
                    let statusBadge = p.status === 'draft' ?
                        '<span class="badge bg-secondary">Draft</span>' :
                        p.status === 'sent' ?
                        '<span class="badge bg-success">Terkirim</span>' :
                        '<span class="badge bg-primary">Dibayar</span>';

                    html += `<div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <div><strong>${formatPeriod(p.period_month)}</strong><br><small>${p.payroll_code}</small></div>
                                <div>${statusBadge}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Pendapatan: </small><span class="text-success">${formatCurrency(p.total_earnings)}</span><br>
                                <small class="text-muted">Potongan: </small><span class="text-danger">${formatCurrency(p.total_deductions)}</span>
                            </div>
                            <div class="fw-bold text-primary mb-2">${formatCurrency(p.net_salary)}</div>
                            <button class="btn btn-sm btn-info btn-view-detail w-100" data-id="${p.id}">
                                <i class='bx bx-show'></i> Lihat Detail
                            </button>
                        </div>
                    </div>`;
                });
                $('#mobileView').html(html);
            }

            function renderPagination(data) {
                if (data.last_page <= 1) {
                    $('#pagination').html('');
                    return;
                }

                let html = '<nav><ul class="pagination justify-content-center">';
                for (let i = 1; i <= data.last_page; i++) {
                    let active = i === data.current_page ? 'active' : '';
                    html +=
                        `<li class="page-item ${active}"><a class="page-link pagination-link" href="#" data-page="${i}">${i}</a></li>`;
                }
                html += '</ul></nav>';
                $('#pagination').html(html);
            }

            function viewDetail(id) {
                $('#detailModal').modal('show');
                $('#detailContent').html(
                    '<div class="text-center py-5"><i class="bx bx-loader bx-spin bx-lg"></i><p>Memuat detail...</p></div>'
                );

                $.get(`/api/employee/payroll/${id}`)
                    .done(function(res) {
                        if (res.success) {
                            let p = res.data;
                            let html = `
                                <div class="text-start">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <h6 class="text-primary"><i class='bx bx-user'></i> Data Karyawan</h6>
                                            <p class="mb-1"><strong>Nama:</strong> ${p.employee.name}</p>
                                            <p class="mb-1"><strong>NIP:</strong> ${p.employee.employee_code}</p>
                                            <p class="mb-1"><strong>Departemen:</strong> ${p.employee.department?.name || '-'}</p>
                                            <p class="mb-1"><strong>Jabatan:</strong> ${p.employee.position?.name || '-'}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-primary"><i class='bx bx-calendar'></i> Periode Payroll</h6>
                                            <p class="mb-1"><strong>Kode:</strong> ${p.payroll_code}</p>
                                            <p class="mb-1"><strong>Periode:</strong> ${formatPeriod(p.period_month)}</p>
                                            <p class="mb-1"><strong>Tanggal Bayar:</strong> ${formatDate(p.payment_date)}</p>
                                            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-${p.status === 'draft' ? 'secondary' : p.status === 'sent' ? 'success' : 'primary'}">${p.status === 'draft' ? 'Draft' : p.status === 'sent' ? 'Terkirim' : 'Dibayar'}</span></p>
                                        </div>
                                    </div>

                                    <hr>
                                    <h6 class="text-success"><i class='bx bx-trending-up'></i> Rincian Pendapatan</h6>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm">
                                            <tr><td>Gaji Pokok</td><td class="text-end">${formatCurrency(p.basic_salary)}</td></tr>
                                            <tr><td>Tunjangan Transport</td><td class="text-end">${formatCurrency(p.allowance_transport)}</td></tr>
                                            <tr><td>Tunjangan Makan</td><td class="text-end">${formatCurrency(p.allowance_meal)}</td></tr>
                                            <tr><td>Tunjangan Jabatan</td><td class="text-end">${formatCurrency(p.allowance_position)}</td></tr>
                                            <tr><td>Tunjangan Lainnya</td><td class="text-end">${formatCurrency(p.allowance_others)}</td></tr>
                                            <tr><td>Uang Lembur</td><td class="text-end">${formatCurrency(p.overtime_pay)}</td></tr>
                                            <tr><td>Bonus</td><td class="text-end">${formatCurrency(p.bonus)}</td></tr>
                                            <tr class="table-success"><td><strong>Total Pendapatan</strong></td><td class="text-end"><strong>${formatCurrency(p.total_earnings)}</strong></td></tr>
                                        </table>
                                    </div>

                                    <h6 class="text-danger"><i class='bx bx-trending-down'></i> Rincian Potongan</h6>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm">
                                            <tr><td>Potongan Terlambat</td><td class="text-end">${formatCurrency(p.deduction_late)}</td></tr>
                                            <tr><td>Potongan Alpha</td><td class="text-end">${formatCurrency(p.deduction_absent)}</td></tr>
                                            <tr><td>Potongan Pinjaman</td><td class="text-end">${formatCurrency(p.deduction_loan)}</td></tr>
                                            <tr><td>Potongan BPJS</td><td class="text-end">${formatCurrency(p.deduction_bpjs)}</td></tr>
                                            <tr><td>Potongan Pajak</td><td class="text-end">${formatCurrency(p.deduction_tax)}</td></tr>
                                            <tr><td>Potongan Lainnya</td><td class="text-end">${formatCurrency(p.deduction_others)}</td></tr>
                                            <tr class="table-danger"><td><strong>Total Potongan</strong></td><td class="text-end"><strong>${formatCurrency(p.total_deductions)}</strong></td></tr>
                                        </table>
                                    </div>

                                    <div class="alert alert-primary">
                                        <h5 class="mb-0"><i class='bx bx-money'></i> Gaji Bersih: <strong class="float-end">${formatCurrency(p.net_salary)}</strong></h5>
                                    </div>

                                    ${p.notes ? `<div class="alert alert-info"><strong>Catatan:</strong> ${p.notes}</div>` : ''}
                                </div>
                            `;
                            $('#detailContent').html(html);
                        }
                    })
                    .fail(function() {
                        $('#detailContent').html(
                            '<div class="alert alert-danger">Gagal memuat detail payroll</div>');
                        toastr.error('Gagal memuat detail payroll');
                    });
            }

            function resetFilters() {
                $('#filterPeriod, #filterStatus, #searchInput').val('');
                loadPayrolls();
            }

            function formatCurrency(value) {
                return 'Rp ' + parseFloat(value || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }

            function formatDate(date) {
                if (!date) return '-';
                return new Date(date).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            }

            function formatPeriod(period) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt',
                    'Nov', 'Des'
                ];
                let [year, month] = period.split('-');
                return `${months[parseInt(month)-1]} ${year}`;
            }

            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func(...args), wait);
                };
            }
        });
    </script>
@endpush
