@extends('layouts.app')

@section('title', 'Face Detection Absensi')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Absensi /</span> Face Detection
            </h4>
        </div>

        <!-- Attendance Card -->
        <div class="row">
            <div class="col-xl-8 col-lg-7 mx-auto">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Absensi dengan Face Detection</h5>
                        <span class="badge bg-primary" id="currentTime"></span>
                    </div>
                    <div class="card-body">
                        <!-- Employee Info -->
                        <div class="mb-4" id="employeeInfo" style="display: none;">
                            <div class="alert alert-info">
                                <h6 class="alert-heading mb-2">Informasi Karyawan</h6>
                                <div class="d-flex align-items-center">
                                    <i class='bx bx-user-circle bx-lg me-3'></i>
                                    <div>
                                        <div class="fw-bold" id="empName"></div>
                                        <small id="empDetails"></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scan Section -->
                        <div class="text-center mb-4" id="scanSection">
                            <div class="mb-3">
                                <label class="form-label">Pilih Karyawan</label>
                                <select class="form-select" id="employeeSelect">
                                    <option value="">-- Pilih Karyawan --</option>
                                </select>
                            </div>

                            <div id="cameraSection" style="display: none;">
                                <!-- Camera Preview -->
                                <div class="position-relative mb-3">
                                    <video id="videoElement" width="100%" height="400" autoplay playsinline
                                        class="rounded border"></video>
                                    <canvas id="canvasElement" style="display: none;"></canvas>

                                    <!-- Face Detection Overlay -->
                                    <div id="faceDetectionOverlay" class="position-absolute top-0 start-0 w-100 h-100"
                                        style="pointer-events: none;">
                                        <!-- Face box will be drawn here -->
                                    </div>
                                </div>

                                <!-- Capture Button -->
                                <div class="d-grid gap-2 mb-3">
                                    <button type="button" class="btn btn-lg btn-primary" id="captureBtn">
                                        <i class='bx bx-camera me-2'></i>
                                        Ambil Foto
                                    </button>
                                </div>

                                <!-- Preview Captured Image -->
                                <div id="capturedPreview" style="display: none;" class="mb-3">
                                    <img id="capturedImage" class="img-fluid rounded border" alt="Captured">
                                    <div class="d-grid gap-2 mt-3">
                                        <button type="button" class="btn btn-success btn-lg" id="checkInBtn">
                                            <i class='bx bx-log-in me-2'></i>
                                            Check In
                                        </button>
                                        <button type="button" class="btn btn-warning btn-lg" id="checkOutBtn">
                                            <i class='bx bx-log-out me-2'></i>
                                            Check Out
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="retakeBtn">
                                            <i class='bx bx-refresh me-2'></i>
                                            Ambil Ulang
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary" id="startCameraBtn">
                                <i class='bx bx-camera me-2'></i>
                                Mulai Kamera
                            </button>
                        </div>

                        <!-- Status Today -->
                        <div id="statusToday" style="display: none;">
                            <div class="alert alert-success">
                                <h6 class="alert-heading mb-2">Status Absensi Hari Ini</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class='bx bx-log-in me-2'></i>
                                            <div>
                                                <small class="text-muted">Check In</small>
                                                <div class="fw-bold" id="checkInTime">-</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class='bx bx-log-out me-2'></i>
                                            <div>
                                                <small class="text-muted">Check Out</small>
                                                <div class="fw-bold" id="checkOutTime">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-label-primary" id="statusBadge"></span>
                                    <span class="badge bg-label-warning" id="lateBadge" style="display: none;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Petunjuk Penggunaan</h6>
                        <ol class="mb-0">
                            <li>Pilih nama karyawan dari dropdown</li>
                            <li>Klik tombol "Mulai Kamera"</li>
                            <li>Posisikan wajah Anda di tengah kamera</li>
                            <li>Pastikan wajah terdeteksi dengan jelas</li>
                            <li>Klik "Ambil Foto" untuk capture</li>
                            <li>Pilih "Check In" atau "Check Out"</li>
                            <li>Sistem akan memverifikasi wajah Anda</li>
                        </ol>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class='bx bx-info-circle me-2'></i>
                            <strong>Catatan:</strong> Pastikan pencahayaan cukup dan wajah terlihat jelas untuk hasil
                            terbaik.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/blazeface"></script>

        <script>
            let video = document.getElementById('videoElement');
            let canvas = document.getElementById('canvasElement');
            let capturedImage = document.getElementById('capturedImage');
            let stream = null;
            let model = null;
            let selectedEmployeeId = null;
            let capturedPhoto = null;

            // Update current time
            function updateTime() {
                const now = new Date();
                document.getElementById('currentTime').textContent = now.toLocaleString('id-ID');
            }
            updateTime();
            setInterval(updateTime, 1000);

            // Load employees
            async function loadEmployees() {
                try {
                    const response = await fetch('/api/karyawan/master-data');
                    const result = await response.json();

                    const select = document.getElementById('employeeSelect');
                    select.innerHTML = '<option value="">-- Pilih Karyawan --</option>';

                    const employees = await fetch('/api/karyawan').then(r => r.json());
                    employees.data.data.forEach(emp => {
                        const option = document.createElement('option');
                        option.value = emp.id;
                        option.textContent = `${emp.employee_code} - ${emp.name}`;
                        select.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error loading employees:', error);
                }
            }

            // Employee selection
            document.getElementById('employeeSelect').addEventListener('change', async function() {
                selectedEmployeeId = this.value;

                if (selectedEmployeeId) {
                    try {
                        const response = await fetch(`/api/karyawan/${selectedEmployeeId}`);
                        const result = await response.json();
                        const emp = result.data;

                        document.getElementById('empName').textContent = emp.name;
                        document.getElementById('empDetails').textContent =
                            `${emp.employee_code} - ${emp.department.name} - ${emp.position.name}`;
                        document.getElementById('employeeInfo').style.display = 'block';

                        // Check today's attendance
                        checkTodayAttendance();
                    } catch (error) {
                        console.error('Error loading employee:', error);
                    }
                } else {
                    document.getElementById('employeeInfo').style.display = 'none';
                }
            });

            // Check today's attendance
            async function checkTodayAttendance() {
                try {
                    const response = await fetch(`/api/attendance/today/${selectedEmployeeId}`);
                    const result = await response.json();

                    if (result.data) {
                        const att = result.data;
                        document.getElementById('statusToday').style.display = 'block';
                        document.getElementById('checkInTime').textContent = att.check_in || '-';
                        document.getElementById('checkOutTime').textContent = att.check_out || '-';
                        document.getElementById('statusBadge').textContent = att.status.toUpperCase();

                        if (att.late_minutes > 0) {
                            document.getElementById('lateBadge').textContent =
                                `Terlambat ${att.late_minutes} menit`;
                            document.getElementById('lateBadge').style.display = 'inline-block';
                        }

                        // Hide check-in button if already checked in
                        if (att.check_in) {
                            document.getElementById('checkInBtn').style.display = 'none';
                        }

                        // Hide check-out button if already checked out
                        if (att.check_out) {
                            document.getElementById('checkOutBtn').style.display = 'none';
                        }
                    } else {
                        document.getElementById('statusToday').style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error checking attendance:', error);
                }
            }

            // Start camera
            document.getElementById('startCameraBtn').addEventListener('click', async function() {
                if (!selectedEmployeeId) {
                    Swal.fire('Error', 'Pilih karyawan terlebih dahulu', 'error');
                    return;
                }

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            width: 640,
                            height: 480,
                            facingMode: 'user'
                        }
                    });
                    video.srcObject = stream;

                    document.getElementById('cameraSection').style.display = 'block';
                    this.style.display = 'none';

                    // Load face detection model
                    if (!model) {
                        model = await blazeface.load();
                        console.log('Face detection model loaded');
                        detectFaces();
                    }
                } catch (error) {
                    console.error('Error accessing camera:', error);
                    Swal.fire('Error', 'Gagal mengakses kamera. Pastikan izin kamera diberikan.', 'error');
                }
            });

            // Face detection loop
            async function detectFaces() {
                if (!video.srcObject) return;

                const predictions = await model.estimateFaces(video, false);

                // Draw face box (optional visual feedback)
                if (predictions.length > 0) {
                    // Face detected - you can add visual feedback here
                }

                requestAnimationFrame(detectFaces);
            }

            // Capture photo
            document.getElementById('captureBtn').addEventListener('click', function() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0);

                capturedPhoto = canvas.toDataURL('image/png');
                capturedImage.src = capturedPhoto;

                document.getElementById('capturedPreview').style.display = 'block';
                video.style.display = 'none';
                document.getElementById('captureBtn').style.display = 'none';
            });

            // Retake photo
            document.getElementById('retakeBtn').addEventListener('click', function() {
                document.getElementById('capturedPreview').style.display = 'none';
                video.style.display = 'block';
                document.getElementById('captureBtn').style.display = 'block';
                capturedPhoto = null;
            });

            // Get current location
            function getCurrentLocation() {
                return new Promise((resolve, reject) => {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            position => resolve({
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude
                            }),
                            error => reject(error)
                        );
                    } else {
                        reject(new Error('Geolocation not supported'));
                    }
                });
            }

            // Check In
            document.getElementById('checkInBtn').addEventListener('click', async function() {
                if (!capturedPhoto) {
                    Swal.fire('Error', 'Ambil foto terlebih dahulu', 'error');
                    return;
                }

                try {
                    const location = await getCurrentLocation();

                    const response = await fetch('/api/attendance/check-in', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            employee_id: selectedEmployeeId,
                            photo: capturedPhoto,
                            latitude: location.latitude,
                            longitude: location.longitude
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire('Berhasil', result.message, 'success');
                        checkTodayAttendance();
                        resetCamera();
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (error) {
                    console.error('Check-in error:', error);
                    Swal.fire('Error', 'Gagal melakukan check-in: ' + error.message, 'error');
                }
            });

            // Check Out
            document.getElementById('checkOutBtn').addEventListener('click', async function() {
                if (!capturedPhoto) {
                    Swal.fire('Error', 'Ambil foto terlebih dahulu', 'error');
                    return;
                }

                try {
                    const location = await getCurrentLocation();

                    const response = await fetch('/api/attendance/check-out', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            employee_id: selectedEmployeeId,
                            photo: capturedPhoto,
                            latitude: location.latitude,
                            longitude: location.longitude
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire('Berhasil', result.message, 'success');
                        checkTodayAttendance();
                        resetCamera();
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (error) {
                    console.error('Check-out error:', error);
                    Swal.fire('Error', 'Gagal melakukan check-out: ' + error.message, 'error');
                }
            });

            // Reset camera
            function resetCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                video.srcObject = null;
                document.getElementById('cameraSection').style.display = 'none';
                document.getElementById('capturedPreview').style.display = 'none';
                document.getElementById('startCameraBtn').style.display = 'block';
                video.style.display = 'block';
                document.getElementById('captureBtn').style.display = 'block';
                capturedPhoto = null;
            }

            // Initialize
            loadEmployees();
        </script>
    @endpush
@endsection
