@extends('layouts.app')

@section('title', 'Daftar Karyawan')

@section('title', 'Daftar Karyawan')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Daftar Karyawan</h4>
                <p class="text-muted mb-0 d-none d-md-block">Kelola data karyawan perusahaan</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class='bx bx-upload me-1'></i> <span class="d-none d-sm-inline">Import</span>
                </button>
                <a href="{{ route('admin.karyawan.export') }}" class="btn btn-success btn-sm">
                    <i class='bx bx-download me-1'></i> <span class="d-none d-sm-inline">Export Excel</span>
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#karyawanModal"
                    onclick="openCreateModal()">
                    <i class='bx bx-plus me-1'></i> <span class="d-none d-sm-inline">Tambah</span>
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Karyawan List Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Data Karyawan</h5>
                <small class="text-muted" id="totalKaryawan">Total: 0</small>
            </div>
            <div class="card-body p-0">
                <!-- Desktop View: Table -->
                <div class="d-none d-md-block">
                    <div class="table-responsive text-nowrap" style="overflow: visible;">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="width: 15%;">Kode</th>
                                    <th style="width: 20%;">Nama</th>
                                    <th style="width: 15%;">Departemen</th>
                                    <th style="width: 15%;">Posisi</th>
                                    <th style="width: 10%;" class="text-center">Status</th>
                                    <th style="width: 60px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0" id="karyawanTableBody">
                                <tr id="loadingRow">
                                    <td colspan="7" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile View: Card List -->
                <div class="d-md-none" id="karyawanCardList">
                    <div class="text-center py-4" id="loadingRowMobile">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="card-footer" id="paginationContainer" style="display: none;">
                <div
                    class="d-flex justify-content-center justify-content-md-between align-items-center flex-column flex-md-row gap-2">
                    <div class="text-muted small d-none d-md-block" id="paginationInfo"></div>
                    <div id="paginationLinks"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form (simplified) -->
    <div class="modal fade" id="karyawanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="karyawanForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Karyawan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="karyawanId">

                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#tabPersonal">
                                    <i class='bx bx-user'></i> Pribadi
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#tabPekerjaan">
                                    <i class='bx bx-briefcase'></i> Pekerjaan
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#tabKontak">
                                    <i class='bx bx-phone'></i> Kontak
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3">
                            <!-- Tab Personal -->
                            <div class="tab-pane fade show active" id="tabPersonal" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="employee_code" class="form-label">Kode Karyawan <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="employee_code"
                                            name="employee_code" required>
                                        <div class="invalid-feedback" id="employee_codeError"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nik" class="form-label">NIK</label>
                                        <input type="text" class="form-control" id="nik" name="nik">
                                        <div class="invalid-feedback" id="nikError"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Lengkap <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <div class="invalid-feedback" id="nameError"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="gender" class="form-label">Jenis Kelamin <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="">Pilih...</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                        <div class="invalid-feedback" id="genderError"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="birth_place" class="form-label">Tempat Lahir <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="birth_place" name="birth_place"
                                            required>
                                        <div class="invalid-feedback" id="birth_placeError"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="birth_date" class="form-label">Tanggal Lahir <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="birth_date" name="birth_date"
                                            required>
                                        <div class="invalid-feedback" id="birth_dateError"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="marital_status" class="form-label">Status Perkawinan <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="marital_status" name="marital_status" required>
                                        <option value="">Pilih...</option>
                                        <option value="Belum Menikah">Belum Menikah</option>
                                        <option value="Menikah">Menikah</option>
                                        <option value="Duda">Duda</option>
                                        <option value="Janda">Janda</option>
                                    </select>
                                    <div class="invalid-feedback" id="marital_statusError"></div>
                                </div>
                            </div>

                            <!-- Tab Pekerjaan -->
                            <div class="tab-pane fade" id="tabPekerjaan" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="department_id" class="form-label">Departemen <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="department_id" name="department_id" required>
                                            <option value="">Pilih Departemen...</option>
                                        </select>
                                        <div class="invalid-feedback" id="department_idError"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="position_id" class="form-label">Posisi <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="position_id" name="position_id" required>
                                            <option value="">Pilih Posisi...</option>
                                        </select>
                                        <div class="invalid-feedback" id="position_idError"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="join_date" class="form-label">Tanggal Bergabung <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="join_date" name="join_date"
                                            required>
                                        <div class="invalid-feedback" id="join_dateError"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="employment_status" class="form-label">Status Kerja <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="employment_status" name="employment_status"
                                            required>
                                            <option value="">Pilih...</option>
                                            <option value="Tetap">Tetap</option>
                                            <option value="Kontrak">Kontrak</option>
                                            <option value="Magang">Magang</option>
                                            <option value="Outsource">Outsource</option>
                                        </select>
                                        <div class="invalid-feedback" id="employment_statusError"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="work_schedule_id" class="form-label">Jadwal Kerja <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="work_schedule_id" name="work_schedule_id"
                                            required>
                                            <option value="">Pilih Jadwal...</option>
                                        </select>
                                        <div class="invalid-feedback" id="work_schedule_idError"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status Karyawan <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Pilih...</option>
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Tidak Aktif</option>
                                        <option value="resign">Resign</option>
                                    </select>
                                    <div class="invalid-feedback" id="statusError"></div>
                                </div>
                            </div>

                            <!-- Tab Kontak -->
                            <div class="tab-pane fade" id="tabKontak" role="tabpanel">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Alamat Lengkap <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                                    <div class="invalid-feedback" id="addressError"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="city" class="form-label">Kota <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="city" name="city"
                                            required>
                                        <div class="invalid-feedback" id="cityError"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="province" class="form-label">Provinsi <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="province" name="province"
                                            required>
                                        <div class="invalid-feedback" id="provinceError"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="postal_code" class="form-label">Kode Pos <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code"
                                            required>
                                        <div class="invalid-feedback" id="postal_codeError"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Nomor HP <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            required>
                                        <div class="invalid-feedback" id="phoneError"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            required>
                                        <div class="invalid-feedback" id="emailError"></div>
                                    </div>
                                </div>

                                <hr>
                                <h6 class="mb-3">Kontak Darurat</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="emergency_contact_name" class="form-label">Nama <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="emergency_contact_name"
                                            name="emergency_contact_name" required>
                                        <div class="invalid-feedback" id="emergency_contact_nameError"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="emergency_contact_phone" class="form-label">Nomor <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="emergency_contact_phone"
                                            name="emergency_contact_phone" required>
                                        <div class="invalid-feedback" id="emergency_contact_phoneError"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="submitSpinner"></span>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Identitas Pribadi</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Kode</th>
                                    <td id="detailEmployeeCode">-</td>
                                </tr>
                                <tr>
                                    <th>NIK</th>
                                    <td id="detailNik">-</td>
                                </tr>
                                <tr>
                                    <th>Nama</th>
                                    <td id="detailName">-</td>
                                </tr>
                                <tr>
                                    <th>Jenis Kelamin</th>
                                    <td id="detailGender">-</td>
                                </tr>
                                <tr>
                                    <th>TTL</th>
                                    <td id="detailBirth">-</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td id="detailMaritalStatus">-</td>
                                </tr>
                            </table>

                            <h6 class="text-primary mb-3 mt-4">Data Pekerjaan</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Departemen</th>
                                    <td id="detailDepartment">-</td>
                                </tr>
                                <tr>
                                    <th>Posisi</th>
                                    <td id="detailPosition">-</td>
                                </tr>
                                <tr>
                                    <th>Tgl Bergabung</th>
                                    <td id="detailJoinDate">-</td>
                                </tr>
                                <tr>
                                    <th>Status Kerja</th>
                                    <td id="detailEmploymentStatus">-</td>
                                </tr>
                                <tr>
                                    <th>Shift</th>
                                    <td id="detailShiftType">-</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td id="detailStatus">-</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Kontak & Alamat</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Alamat</th>
                                    <td id="detailAddress">-</td>
                                </tr>
                                <tr>
                                    <th>Kota</th>
                                    <td id="detailCity">-</td>
                                </tr>
                                <tr>
                                    <th>Provinsi</th>
                                    <td id="detailProvince">-</td>
                                </tr>
                                <tr>
                                    <th>Kode Pos</th>
                                    <td id="detailPostalCode">-</td>
                                </tr>
                                <tr>
                                    <th>HP</th>
                                    <td id="detailPhone">-</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td id="detailEmail">-</td>
                                </tr>
                            </table>

                            <h6 class="text-primary mb-3 mt-4">Kontak Darurat</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Nama</th>
                                    <td id="detailEmergencyName">-</td>
                                </tr>
                                <tr>
                                    <th>Nomor</th>
                                    <td id="detailEmergencyPhone">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="importForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Data Karyawan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class='bx bx-info-circle me-2'></i>
                            <strong>Petunjuk:</strong>
                            <ol class="mb-0 mt-2 ps-3">
                                <li>Download template Excel terlebih dahulu</li>
                                <li>Isi data karyawan sesuai format template</li>
                                <li>Upload file Excel yang sudah diisi</li>
                                <li>Pastikan Departemen dan Posisi sudah ada di database</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <a href="{{ route('admin.karyawan.template') }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class='bx bx-download me-1'></i> Download Template Excel
                            </a>
                        </div>

                        <div class="mb-3">
                            <label for="import_file" class="form-label">Pilih File Excel <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="import_file" name="file"
                                accept=".xlsx,.xls" required>
                            <small class="text-muted">Format: .xlsx atau .xls (Max 2MB)</small>
                            <div class="invalid-feedback" id="fileError"></div>
                        </div>

                        <div id="importProgress" class="d-none">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 100%"></div>
                            </div>
                            <p class="text-center mt-2 mb-0">Sedang mengimport data...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="importBtn">
                            <i class='bx bx-upload me-1'></i> Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentPage = 1;
        let karyawanModal, detailModal, importModal;
        let masterData = {};

        $(document).ready(function() {
            karyawanModal = new bootstrap.Modal(document.getElementById('karyawanModal'));
            detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
            importModal = new bootstrap.Modal(document.getElementById('importModal'));
            loadMasterData();
            loadKaryawans();
            $('#karyawanForm').on('submit', function(e) {
                e.preventDefault();
                saveKaryawan();
            });
            $('#importForm').on('submit', function(e) {
                e.preventDefault();
                importKaryawan();
            });
        });

        function loadMasterData() {
            $.ajax({
                url: '/api/karyawan/master-data',
                method: 'GET',
                success: function(response) {
                    masterData = response.data;
                    populateDepartments();
                    populatePositions();
                    populateWorkSchedules();
                }
            });
        }

        function populateDepartments() {
            const select = $('#department_id');
            select.find('option:not(:first)').remove();
            masterData.departments.forEach(dept => {
                select.append(`<option value="${dept.id}">${dept.name}</option>`);
            });
        }

        function populatePositions() {
            const select = $('#position_id');
            select.find('option:not(:first)').remove();
            masterData.positions.forEach(pos => {
                select.append(`<option value="${pos.id}">${pos.name}</option>`);
            });
        }

        function populateWorkSchedules() {
            const select = $('#work_schedule_id');
            select.find('option:not(:first)').remove();
            masterData.work_schedules.forEach(schedule => {
                select.append(`<option value="${schedule.id}">${schedule.name}</option>`);
            });
        }

        function loadKaryawans(page = 1) {
            $.ajax({
                url: '/api/karyawan?page=' + page + '&per_page=10',
                method: 'GET',
                beforeSend: function() {
                    $('#loadingRow').show();
                    $('#loadingRowMobile').show();
                },
                success: function(response) {
                    renderKaryawans(response.data);
                    currentPage = page;
                },
                error: function() {
                    showAlert('Gagal memuat data karyawan', 'danger');
                }
            });
        }

        function renderKaryawans(data) {
            const tbody = $('#karyawanTableBody');
            const cardList = $('#karyawanCardList');
            tbody.empty();
            cardList.empty();

            if (data.data.length === 0) {
                tbody.append(`
                    <tr><td colspan="7" class="text-center py-4">
                        <div class="mb-3"><i class='bx bx-user' style="font-size: 48px; color: #ddd;"></i></div>
                        <p class="text-muted mb-2">Belum ada data karyawan</p>
                        <button class="btn btn-sm btn-primary" onclick="openCreateModal()" data-bs-toggle="modal" data-bs-target="#karyawanModal">
                            <i class='bx bx-plus me-1'></i> Tambah Karyawan
                        </button>
                    </td></tr>
                `);
                cardList.append(`
                    <div class="text-center py-4">
                        <div class="mb-3"><i class='bx bx-user' style="font-size: 48px; color: #ddd;"></i></div>
                        <p class="text-muted mb-2">Belum ada data karyawan</p>
                        <button class="btn btn-sm btn-primary" onclick="openCreateModal()" data-bs-toggle="modal" data-bs-target="#karyawanModal">
                            <i class='bx bx-plus me-1'></i> Tambah Karyawan
                        </button>
                    </div>
                `);
                $('#paginationContainer').hide();
            } else {
                data.data.forEach((k, index) => {
                    const rowNumber = data.from + index;
                    const statusBadge = getStatusBadge(k.status);

                    tbody.append(`
                        <tr>
                            <td style="white-space: nowrap;">${rowNumber}</td>
                            <td style="white-space: nowrap;"><strong>${k.employee_code}</strong></td>
                            <td><strong>${k.name}</strong></td>
                            <td>${k.department ? k.department.name : '-'}</td>
                            <td>${k.position ? k.position.name : '-'}</td>
                            <td class="text-center" style="white-space: nowrap;">${statusBadge}</td>
                            <td class="text-center" style="white-space: nowrap; position: relative;">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bx bx-dots-vertical-rounded'></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="showDetail(${k.id})">
                                            <i class='bx bx-show me-2'></i> Detail</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="editKaryawan(${k.id})">
                                            <i class='bx bx-edit me-2'></i> Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteKaryawan(${k.id})">
                                            <i class='bx bx-trash me-2'></i> Hapus</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `);

                    cardList.append(`
                        <div class="card mb-2 mx-3 border">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">${k.name}</h6>
                                        <p class="text-muted small mb-1">${k.employee_code}</p>
                                        <p class="text-muted small mb-2">${k.department ? k.department.name : '-'} - ${k.position ? k.position.name : '-'}</p>
                                        ${statusBadge}
                                    </div>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-dots-vertical-rounded'></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="showDetail(${k.id})">
                                                <i class='bx bx-show me-2'></i> Detail</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="editKaryawan(${k.id})">
                                                <i class='bx bx-edit me-2'></i> Edit</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteKaryawan(${k.id})">
                                                <i class='bx bx-trash me-2'></i> Hapus</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                });

                $('#totalKaryawan').text(`Total: ${data.total}`);
                $('#paginationInfo').text(`${data.from} - ${data.to} dari ${data.total}`);

                if (data.last_page > 1) {
                    renderPagination(data);
                    $('#paginationContainer').show();
                } else {
                    $('#paginationContainer').hide();
                }
            }
        }

        function getStatusBadge(status) {
            const badges = {
                'active': '<span class="badge bg-label-success">Aktif</span>',
                'inactive': '<span class="badge bg-label-warning">Tidak Aktif</span>',
                'resign': '<span class="badge bg-label-danger">Resign</span>'
            };
            return badges[status] || status;
        }

        function renderPagination(data) {
            const container = $('#paginationLinks');
            container.empty();
            let html = '<nav><ul class="pagination pagination-sm mb-0">';
            html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadKaryawans(${data.current_page - 1}); return false;">
                    <i class='bx bx-chevron-left'></i></a></li>`;

            const maxVisiblePages = window.innerWidth < 768 ? 3 : 5;
            const halfVisible = Math.floor(maxVisiblePages / 2);
            let startPage = Math.max(1, data.current_page - halfVisible);
            let endPage = Math.min(data.last_page, startPage + maxVisiblePages - 1);

            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                html +=
                    `<li class="page-item"><a class="page-link" href="#" onclick="loadKaryawans(1); return false;">1</a></li>`;
                if (startPage > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadKaryawans(${i}); return false;">${i}</a></li>`;
            }

            if (endPage < data.last_page) {
                if (endPage < data.last_page - 1) html +=
                    '<li class="page-item disabled"><span class="page-link">...</span></li>';
                html +=
                    `<li class="page-item"><a class="page-link" href="#" onclick="loadKaryawans(${data.last_page}); return false;">${data.last_page}</a></li>`;
            }

            html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadKaryawans(${data.current_page + 1}); return false;">
                    <i class='bx bx-chevron-right'></i></a></li>`;
            html += '</ul></nav>';
            container.html(html);
        }

        function openCreateModal() {
            $('#modalTitle').text('Tambah Karyawan');
            $('#karyawanId').val('');
            $('#karyawanForm')[0].reset();
            $('.form-control, .form-select').removeClass('is-invalid');
            $('.nav-tabs button:first').tab('show');
        }

        function editKaryawan(id) {
            $.ajax({
                url: `/api/karyawan/${id}`,
                method: 'GET',
                success: function(response) {
                    const k = response.data;
                    $('#modalTitle').text('Edit Karyawan');
                    $('#karyawanId').val(k.id);
                    $('#employee_code').val(k.employee_code);
                    $('#nik').val(k.nik);
                    $('#name').val(k.name);
                    $('#gender').val(k.gender);
                    $('#birth_place').val(k.birth_place);

                    // Format tanggal untuk input type="date" (YYYY-MM-DD)
                    if (k.birth_date) {
                        const birthDate = new Date(k.birth_date);
                        $('#birth_date').val(birthDate.toISOString().split('T')[0]);
                    }

                    $('#marital_status').val(k.marital_status);
                    $('#department_id').val(k.department_id);
                    $('#position_id').val(k.position_id);

                    // Format tanggal bergabung
                    if (k.join_date) {
                        const joinDate = new Date(k.join_date);
                        $('#join_date').val(joinDate.toISOString().split('T')[0]);
                    }

                    $('#employment_status').val(k.employment_status);
                    $('#work_schedule_id').val(k.work_schedule_id);
                    $('#address').val(k.address);
                    $('#city').val(k.city);
                    $('#province').val(k.province);
                    $('#postal_code').val(k.postal_code);
                    $('#phone').val(k.phone);
                    $('#email').val(k.email);
                    $('#emergency_contact_name').val(k.emergency_contact_name);
                    $('#emergency_contact_phone').val(k.emergency_contact_phone);
                    $('#status').val(k.status);
                    $('.form-control, .form-select').removeClass('is-invalid');
                    $('.nav-tabs button:first').tab('show');
                    karyawanModal.show();
                }
            });
        }

        function saveKaryawan() {
            const id = $('#karyawanId').val();
            const url = id ? `/api/karyawan/${id}` : '/api/karyawan';
            const method = id ? 'PUT' : 'POST';

            const data = {
                employee_code: $('#employee_code').val(),
                nik: $('#nik').val(),
                name: $('#name').val(),
                gender: $('#gender').val(),
                birth_place: $('#birth_place').val(),
                birth_date: $('#birth_date').val(),
                marital_status: $('#marital_status').val(),
                department_id: $('#department_id').val(),
                position_id: $('#position_id').val(),
                join_date: $('#join_date').val(),
                employment_status: $('#employment_status').val(),
                work_schedule_id: $('#work_schedule_id').val(),
                address: $('#address').val(),
                city: $('#city').val(),
                province: $('#province').val(),
                postal_code: $('#postal_code').val(),
                phone: $('#phone').val(),
                email: $('#email').val(),
                emergency_contact_name: $('#emergency_contact_name').val(),
                emergency_contact_phone: $('#emergency_contact_phone').val(),
                status: $('#status').val()
            };

            $('.form-control, .form-select').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            $('#submitBtn').prop('disabled', true);
            $('#submitSpinner').removeClass('d-none');

            $.ajax({
                url: url,
                method: method,
                data: data,
                success: function(response) {
                    karyawanModal.hide();
                    showAlert(response.message, 'success');
                    loadKaryawans(currentPage);
                    $('#karyawanForm')[0].reset();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        for (let field in errors) {
                            $(`#${field}`).addClass('is-invalid');
                            $(`#${field}Error`).text(errors[field][0]);
                        }
                    } else {
                        showAlert(xhr.responseJSON.message || 'Terjadi kesalahan', 'danger');
                    }
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false);
                    $('#submitSpinner').addClass('d-none');
                }
            });
        }

        function showDetail(id) {
            $.ajax({
                url: `/api/karyawan/${id}`,
                method: 'GET',
                success: function(response) {
                    const k = response.data;

                    // Format tanggal lahir (hanya tanggal, tanpa jam)
                    let birthDateFormatted = k.birth_date;
                    if (k.birth_date) {
                        const birthDate = new Date(k.birth_date);
                        birthDateFormatted = birthDate.toISOString().split('T')[0];
                    }

                    // Format tanggal bergabung (hanya tanggal, tanpa jam)
                    let joinDateFormatted = k.join_date;
                    if (k.join_date) {
                        const joinDate = new Date(k.join_date);
                        joinDateFormatted = joinDate.toISOString().split('T')[0];
                    }

                    $('#detailEmployeeCode').text(k.employee_code);
                    $('#detailNik').text(k.nik || '-');
                    $('#detailName').text(k.name);
                    $('#detailGender').text(k.gender === 'L' ? 'Laki-laki' : 'Perempuan');
                    $('#detailBirth').text(`${k.birth_place}, ${birthDateFormatted}`);
                    $('#detailMaritalStatus').text(k.marital_status);
                    $('#detailDepartment').text(k.department ? k.department.name : '-');
                    $('#detailPosition').text(k.position ? k.position.name : '-');
                    $('#detailJoinDate').text(joinDateFormatted);
                    $('#detailEmploymentStatus').text(k.employment_status);
                    $('#detailShiftType').text(k.work_schedule ? k.work_schedule.name : '-');
                    $('#detailStatus').html(getStatusBadge(k.status));
                    $('#detailAddress').text(k.address);
                    $('#detailCity').text(k.city);
                    $('#detailProvince').text(k.province);
                    $('#detailPostalCode').text(k.postal_code);
                    $('#detailPhone').text(k.phone);
                    $('#detailEmail').text(k.email);
                    $('#detailEmergencyName').text(k.emergency_contact_name);
                    $('#detailEmergencyPhone').text(k.emergency_contact_phone);
                    detailModal.show();
                }
            });
        }

        function deleteKaryawan(id) {
            Swal.fire({
                title: 'Hapus Karyawan?',
                html: 'Apakah Anda yakin ingin menghapus karyawan ini?<br><br><small class="text-danger">Peringatan: Data akun user juga akan terhapus.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="bx bx-trash"></i> Ya, Hapus!',
                cancelButtonText: '<i class="bx bx-x"></i> Batal',
                buttonsStyling: true,
                reverseButtons: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                backdrop: true,
                focusConfirm: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/api/karyawan/${id}`,
                        method: 'DELETE',
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false,
                                allowOutsideClick: false
                            });
                            loadKaryawans(currentPage);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: xhr.responseJSON.message || 'Gagal menghapus karyawan',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            });
                        }
                    });
                }
            });
        }

        function showAlert(message, type) {
            const icon = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';
            const alert = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class='bx ${icon} me-2'></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('#alertContainer').html(alert);
            setTimeout(function() {
                $('.alert').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }

        function importKaryawan() {
            const fileInput = $('#import_file')[0];

            if (!fileInput.files.length) {
                $('#fileError').text('File Excel harus dipilih');
                $('#import_file').addClass('is-invalid');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            $('#import_file').removeClass('is-invalid');
            $('#fileError').text('');
            $('#importBtn').prop('disabled', true);
            $('#importProgress').removeClass('d-none');

            $.ajax({
                url: '{{ route('admin.karyawan.import') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    importModal.hide();
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#importForm')[0].reset();
                    loadKaryawans(1);
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat import';

                    if (xhr.status === 422) {
                        if (xhr.responseJSON.errors) {
                            errorMessage = '<ul class="mb-0 ps-3">';
                            xhr.responseJSON.errors.forEach(error => {
                                errorMessage += `<li>${error}</li>`;
                            });
                            errorMessage += '</ul>';
                        } else {
                            errorMessage = xhr.responseJSON.message;
                        }
                    }

                    Swal.fire({
                        title: 'Gagal Import!',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    $('#importBtn').prop('disabled', false);
                    $('#importProgress').addClass('d-none');
                }
            });
        }
    </script>
@endpush
