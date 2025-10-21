<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawans;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Exports\KaryawanExport;
use App\Exports\KaryawanTemplateExport;
use App\Imports\KaryawanImport;
use Maatwebsite\Excel\Facades\Excel;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        return view('admin.karyawan.index');
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $karyawans = Karyawans::with(['department', 'position'])
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $karyawans
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_code' => 'required|string|max:20|unique:employees,employee_code',
            'nik' => 'nullable|string|max:20',
            'name' => 'required|string|max:100',
            'gender' => 'required|in:L,P',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'marital_status' => 'required|in:Belum Menikah,Menikah,Duda,Janda',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'join_date' => 'required|date',
            'employment_status' => 'required|in:Tetap,Kontrak,Magang,Outsource',
            'shift_type' => 'required|in:Pagi,Sore,Malam,Rotasi',
            'address' => 'required|string',
            'city' => 'required|string|max:50',
            'province' => 'required|string|max:50',
            'postal_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:100|unique:employees,email',
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,resign',
        ], [
            'employee_code.required' => 'Kode karyawan wajib diisi',
            'employee_code.unique' => 'Kode karyawan sudah ada',
            'name.required' => 'Nama karyawan wajib diisi',
            'gender.required' => 'Jenis kelamin wajib dipilih',
            'birth_place.required' => 'Tempat lahir wajib diisi',
            'birth_date.required' => 'Tanggal lahir wajib diisi',
            'marital_status.required' => 'Status perkawinan wajib dipilih',
            'department_id.required' => 'Departemen wajib dipilih',
            'position_id.required' => 'Posisi wajib dipilih',
            'join_date.required' => 'Tanggal bergabung wajib diisi',
            'employment_status.required' => 'Status kerja wajib dipilih',
            'shift_type.required' => 'Jenis shift wajib dipilih',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah terdaftar',
            'phone.required' => 'Nomor HP wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create user account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password123'), // Default password
            ]);

            // Create employee
            $data = $request->all();
            $data['user_id'] = $user->id;

            $karyawan = Karyawans::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil ditambahkan',
                'data' => $karyawan->load(['department', 'position'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan karyawan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single karyawan
     */
    public function show($id)
    {
        $karyawan = Karyawans::with(['department', 'position', 'supervisor'])->find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $karyawan
        ]);
    }

    /**
     * Update karyawan
     */
    public function update(Request $request, $id)
    {
        $karyawan = Karyawans::find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'employee_code' => 'required|string|max:20|unique:employees,employee_code,' . $id,
            'nik' => 'nullable|string|max:20',
            'name' => 'required|string|max:100',
            'gender' => 'required|in:L,P',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'marital_status' => 'required|in:Belum Menikah,Menikah,Duda,Janda',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'join_date' => 'required|date',
            'employment_status' => 'required|in:Tetap,Kontrak,Magang,Outsource',
            'shift_type' => 'required|in:Pagi,Sore,Malam,Rotasi',
            'address' => 'required|string',
            'city' => 'required|string|max:50',
            'province' => 'required|string|max:50',
            'postal_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:100|unique:employees,email,' . $id,
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,resign',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $karyawan->update($request->all());

            // Update user if email changed
            if ($karyawan->user && $karyawan->user->email !== $request->email) {
                $karyawan->user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil diperbarui',
                'data' => $karyawan->load(['department', 'position'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui karyawan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete karyawan
     */
    public function destroy($id)
    {
        $karyawan = Karyawans::find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Delete user account
            if ($karyawan->user) {
                $karyawan->user->delete();
            }

            $karyawan->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus karyawan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get master data for dropdowns
     */
    public function getMasterData()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'departments' => Department::orderBy('name')->get(),
                'positions' => Position::orderBy('name')->get(),
                'supervisors' => Karyawans::where('status', 'active')
                    ->orderBy('name')
                    ->get(['id', 'name', 'employee_code'])
            ]
        ]);
    }

    /**
     * Export karyawan to Excel
     */
    public function export()
    {
        return Excel::download(new KaryawanExport, 'Data_Karyawan_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Import karyawan from Excel
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:2048'
        ], [
            'file.required' => 'File Excel harus dipilih',
            'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls)',
            'file.max' => 'Ukuran file maksimal 2MB'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $import = new KaryawanImport();
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            $errors = $import->errors();

            if (count($failures) > 0 || count($errors) > 0) {
                $errorMessages = [];

                foreach ($failures as $failure) {
                    $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                }

                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Import selesai dengan error',
                    'errors' => $errorMessages
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diimport'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat import: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        return Excel::download(new KaryawanTemplateExport, 'Template_Import_Karyawan.xlsx');
    }
}
