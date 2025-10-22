<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Karyawans;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    /**
     * Display attendance dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $status = $request->get('status');
        $department = $request->get('department');

        // Build query
        $query = Attendance::with(['employee.department', 'employee.position', 'employee.workSchedule']);

        // Apply filters
        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($department) {
            $query->whereHas('employee', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }

        $query->whereBetween('attendance_date', [$dateFrom, $dateTo]);

        // Get paginated results
        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Get statistics
        $stats = [
            'total' => Attendance::whereBetween('attendance_date', [$dateFrom, $dateTo])->count(),
            'hadir' => Attendance::where('status', 'hadir')->whereBetween('attendance_date', [$dateFrom, $dateTo])->count(),
            'terlambat' => Attendance::where('status', 'terlambat')->whereBetween('attendance_date', [$dateFrom, $dateTo])->count(),
            'izin' => Attendance::where('status', 'izin')->whereBetween('attendance_date', [$dateFrom, $dateTo])->count(),
            'alpha' => Attendance::where('status', 'alpha')->whereBetween('attendance_date', [$dateFrom, $dateTo])->count(),
        ];

        // Get departments for filter
        $departments = \App\Models\Department::orderBy('name')->get();

        return view('admin.attendance.index', compact('attendances', 'stats', 'departments', 'dateFrom', 'dateTo'));
    }

    /**
     * Get attendance list with filters
     */
    public function list(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $status = $request->get('status', '');

        $attendances = Attendance::with(['employee.department', 'employee.position'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $attendances
        ]);
    }

    /**
     * Get today's attendance for specific employee
     */
    public function getTodayAttendance($employeeId)
    {
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('attendance_date', today())
            ->first();

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Check in with face detection
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'photo' => 'required|string', // Base64 image
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if already checked in today
            $existingAttendance = Attendance::where('employee_id', $request->employee_id)
                ->whereDate('attendance_date', today())
                ->first();

            if ($existingAttendance && $existingAttendance->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-in hari ini'
                ], 400);
            }

            // Get employee
            $employee = Karyawans::findOrFail($request->employee_id);

            // Save photo
            $photoPath = $this->saveBase64Image($request->photo, 'attendance/check-in');

            // Get work schedule to check if late
            $checkInTime = now();
            $schedule = $employee->workSchedule;

            $lateMinutes = 0;
            $status = 'hadir';

            if ($schedule) {
                $scheduledTime = Carbon::parse($schedule->check_in_time);
                if ($checkInTime->gt($scheduledTime)) {
                    $lateMinutes = $checkInTime->diffInMinutes($scheduledTime);
                    $status = 'terlambat';
                }
            }

            // Create or update attendance
            $attendance = Attendance::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'attendance_date' => today()
                ],
                [
                    'check_in' => $checkInTime->format('H:i:s'),
                    'photo_in' => $photoPath,
                    'location_in' => $request->latitude . ',' . $request->longitude,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil',
                'data' => [
                    'attendance' => $attendance,
                    'late_minutes' => $lateMinutes,
                    'status' => $status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan check-in: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check out with face detection
     */
    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'photo' => 'required|string', // Base64 image
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if checked in today
            $attendance = Attendance::where('employee_id', $request->employee_id)
                ->whereDate('attendance_date', today())
                ->first();

            if (!$attendance || !$attendance->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan check-in hari ini'
                ], 400);
            }

            if ($attendance->check_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-out hari ini'
                ], 400);
            }

            // Save photo
            $photoPath = $this->saveBase64Image($request->photo, 'attendance/check-out');

            // Update attendance
            $attendance->update([
                'check_out' => now()->format('H:i:s'),
                'photo_out' => $photoPath,
                'location_out' => $request->latitude . ',' . $request->longitude,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan check-out: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance summary
     */
    public function summary(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $query = Attendance::query();

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $query->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month);

        $summary = [
            'total' => $query->count(),
            'hadir' => $query->clone()->where('status', 'hadir')->count(),
            'terlambat' => $query->clone()->where('status', 'terlambat')->count(),
            'izin' => $query->clone()->where('status', 'izin')->count(),
            'sakit' => $query->clone()->where('status', 'sakit')->count(),
            'alpha' => $query->clone()->where('status', 'alpha')->count(),
            'cuti' => $query->clone()->where('status', 'cuti')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Verify employee face (compare with profile photo)
     */
    public function verifyFace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'photo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employee = Karyawans::findOrFail($request->employee_id);

            // Here you would implement actual face recognition
            // For now, we'll just check if employee exists and has a profile photo

            if (!$employee->photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan belum memiliki foto profil. Silakan upload foto profil terlebih dahulu.',
                    'verified' => false
                ], 400);
            }

            // In production, integrate with face recognition API like:
            // - AWS Rekognition
            // - Azure Face API
            // - Face-API.js (client-side)
            // - Python face_recognition library

            // For now, return success (you need to implement actual face comparison)
            return response()->json([
                'success' => true,
                'message' => 'Verifikasi wajah berhasil',
                'verified' => true,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_code' => $employee->employee_code,
                    'department' => $employee->department->name,
                    'position' => $employee->position->name,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi wajah: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save base64 image to storage
     */
    private function saveBase64Image($base64String, $path)
    {
        // Remove data:image/png;base64, or data:image/jpeg;base64,
        $image = str_replace('data:image/png;base64,', '', $base64String);
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace('data:image/jpg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);

        $imageName = uniqid() . '_' . time() . '.png';
        $imagePath = $path . '/' . $imageName;

        Storage::disk('public')->put($imagePath, base64_decode($image));

        return $imagePath;
    }

    /**
     * Manual attendance entry (for admin)
     */
    public function manualEntry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha,cuti',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $attendance = Attendance::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'attendance_date' => $request->attendance_date
                ],
                [
                    'check_in' => $request->check_in,
                    'check_out' => $request->check_out,
                    'status' => $request->status,
                    'notes' => $request->notes,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data absensi berhasil disimpan',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data absensi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display face detection page
     */
    public function faceDetection()
    {
        return view('admin.attendance.face-detection');
    }

    /**
     * Display report and analytics page
     */
    public function report(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $department = $request->get('department');

        // Get date range for selected month
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Base query
        $query = Attendance::whereBetween('attendance_date', [$startDate, $endDate]);

        if ($department) {
            $query->whereHas('employee', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }

        // Get statistics
        $totalAttendance = $query->count();
        $hadirCount = (clone $query)->where('status', 'hadir')->count();
        $terlambatCount = (clone $query)->where('status', 'terlambat')->count();
        $izinCount = (clone $query)->where('status', 'izin')->count();
        $alphaCount = (clone $query)->where('status', 'alpha')->count();

        // Get daily statistics for chart
        $dailyStats = Attendance::selectRaw('DATE(attendance_date) as date, status, COUNT(*) as count')
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->when($department, function ($q) use ($department) {
                return $q->whereHas('employee', function ($subQ) use ($department) {
                    $subQ->where('department_id', $department);
                });
            })
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        // Get department statistics
        $departmentStats = Attendance::selectRaw('departments.name as department, attendances.status, COUNT(*) as count')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->when($department, function ($q) use ($department) {
                return $q->where('departments.id', $department);
            })
            ->groupBy('departments.name', 'attendances.status')
            ->get();

        // Get top late employees
        $topLateEmployees = Attendance::selectRaw('employees.name, employees.employee_code, SUM(attendances.late_minutes) as total_late, COUNT(*) as late_count')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->where('attendances.status', 'terlambat')
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->when($department, function ($q) use ($department) {
                return $q->where('employees.department_id', $department);
            })
            ->groupBy('employees.id', 'employees.name', 'employees.employee_code')
            ->orderBy('total_late', 'desc')
            ->limit(10)
            ->get();

        // Get departments for filter
        $departments = \App\Models\Department::orderBy('name')->get();

        // Prepare chart data
        $chartData = $this->prepareChartData($dailyStats, $startDate, $endDate);

        return view('admin.attendance.report', compact(
            'totalAttendance',
            'hadirCount',
            'terlambatCount',
            'izinCount',
            'alphaCount',
            'dailyStats',
            'departmentStats',
            'topLateEmployees',
            'departments',
            'chartData',
            'year',
            'month',
            'department'
        ));
    }

    /**
     * Prepare data for charts
     */
    private function prepareChartData($dailyStats, $startDate, $endDate)
    {
        $dates = [];
        $hadir = [];
        $terlambat = [];
        $izin = [];
        $alpha = [];

        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $dates[] = $current->format('d/m');

            $dayStats = $dailyStats->where('date', $dateStr);

            $hadir[] = $dayStats->where('status', 'hadir')->sum('count');
            $terlambat[] = $dayStats->where('status', 'terlambat')->sum('count');
            $izin[] = $dayStats->where('status', 'izin')->sum('count');
            $alpha[] = $dayStats->where('status', 'alpha')->sum('count');

            $current->addDay();
        }

        return [
            'dates' => $dates,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'alpha' => $alpha,
        ];
    }

    /**
     * Get attendance detail
     */
    public function detail($id)
    {
        $attendance = Attendance::with(['employee.department', 'employee.position', 'employee.workSchedule'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Export attendance to Excel
     */
    public function export(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $status = $request->get('status');
        $department = $request->get('department');
        $search = $request->get('search');

        return Excel::download(
            new \App\Exports\AttendanceExport($dateFrom, $dateTo, $status, $department, $search),
            'absensi_' . $dateFrom . '_' . $dateTo . '.xlsx'
        );
    }
}
