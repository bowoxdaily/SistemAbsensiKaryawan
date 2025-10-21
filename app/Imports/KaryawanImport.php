<?php

namespace App\Imports;

use App\Models\Karyawans;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class KaryawanImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $departmentCache = [];
    protected $positionCache = [];

    public function __construct()
    {
        // Cache departments and positions to avoid repeated queries
        $this->departmentCache = Department::pluck('id', 'name')->toArray();
        $this->positionCache = Position::pluck('id', 'name')->toArray();
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip example row (John Doe / baris contoh)
        if (isset($row['kode_karyawan']) && strtoupper(trim($row['kode_karyawan'])) === 'EMP001') {
            return null;
        }

        // Skip empty rows
        if (empty($row['kode_karyawan']) || empty($row['nama_lengkap'])) {
            return null;
        }

        // Manual validation for required fields (since we made rules nullable to skip validation for empty rows)
        $requiredFields = [
            'kode_karyawan',
            'nama_lengkap',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'status_perkawinan',
            'departemen',
            'posisi',
            'tanggal_bergabung',
            'status_kerja',
            'jenis_shift',
            'status',
            'alamat',
            'kota',
            'provinsi',
            'kode_pos',
            'no_hp',
            'email',
            'kontak_darurat_nama',
            'kontak_darurat_no'
        ];

        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                throw new \Exception("Field {$field} is required");
            }
        }

        // Get department ID
        $departmentId = $this->departmentCache[$row['departemen']] ?? null;

        // Get position ID
        $positionId = $this->positionCache[$row['posisi']] ?? null;

        // Convert gender
        $gender = $this->convertGender($row['jenis_kelamin']);

        // Convert status
        $status = $this->convertStatus($row['status']);

        DB::beginTransaction();
        try {
            // Create user account
            $user = User::create([
                'name' => $row['nama_lengkap'],
                'email' => $row['email'],
                'password' => Hash::make('password123'),
            ]);

            // Create employee
            $karyawan = new Karyawans([
                'employee_code' => $row['kode_karyawan'],
                'nik' => $row['nik'] ?? null,
                'name' => $row['nama_lengkap'],
                'gender' => $gender,
                'birth_place' => $row['tempat_lahir'],
                'birth_date' => $this->convertDate($row['tanggal_lahir']),
                'marital_status' => $row['status_perkawinan'],
                'department_id' => $departmentId,
                'position_id' => $positionId,
                'join_date' => $this->convertDate($row['tanggal_bergabung']),
                'employment_status' => $row['status_kerja'],
                'shift_type' => $row['jenis_shift'],
                'status' => $status,
                'address' => $row['alamat'],
                'city' => $row['kota'],
                'province' => $row['provinsi'],
                'postal_code' => $row['kode_pos'],
                'phone' => $row['no_hp'],
                'email' => $row['email'],
                'emergency_contact_name' => $row['kontak_darurat_nama'],
                'emergency_contact_phone' => $row['kontak_darurat_no'],
                'user_id' => $user->id,
            ]);

            DB::commit();
            return $karyawan;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'kode_karyawan' => 'nullable|unique:employees,employee_code',
            'nama_lengkap' => 'nullable',
            'jenis_kelamin' => 'nullable',
            'tempat_lahir' => 'nullable',
            'tanggal_lahir' => 'nullable',
            'status_perkawinan' => 'nullable',
            'departemen' => 'nullable',
            'posisi' => 'nullable',
            'tanggal_bergabung' => 'nullable',
            'status_kerja' => 'nullable',
            'jenis_shift' => 'nullable',
            'status' => 'nullable',
            'alamat' => 'nullable',
            'kota' => 'nullable',
            'provinsi' => 'nullable',
            'kode_pos' => 'nullable',
            'no_hp' => 'nullable',
            'email' => 'nullable|email|unique:employees,email',
            'kontak_darurat_nama' => 'nullable',
            'kontak_darurat_no' => 'nullable',
        ];
    }

    /**
     * Prepare data for validation
     */
    public function prepareForValidation($data, $index)
    {
        // Skip validation for example row or empty rows
        if ((isset($data['kode_karyawan']) && strtoupper(trim($data['kode_karyawan'])) === 'EMP001') ||
            empty($data['kode_karyawan']) ||
            empty($data['nama_lengkap'])
        ) {
            return [];
        }

        return $data;
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'kode_karyawan.required' => 'Kode karyawan harus diisi',
            'kode_karyawan.unique' => 'Kode karyawan sudah terdaftar',
            'email.unique' => 'Email sudah terdaftar',
            'email.email' => 'Format email tidak valid',
        ];
    }

    /**
     * Convert gender text to code
     */
    private function convertGender($gender)
    {
        $gender = strtolower(trim($gender));
        if (in_array($gender, ['l', 'laki-laki', 'laki', 'male'])) {
            return 'L';
        } elseif (in_array($gender, ['p', 'perempuan', 'wanita', 'female'])) {
            return 'P';
        }
        return 'L'; // Default
    }

    /**
     * Convert status text to code
     */
    private function convertStatus($status)
    {
        $status = strtolower(trim($status));
        if (in_array($status, ['aktif', 'active'])) {
            return 'active';
        } elseif (in_array($status, ['tidak aktif', 'inactive', 'nonaktif'])) {
            return 'inactive';
        } elseif (in_array($status, ['resign', 'keluar'])) {
            return 'resign';
        }
        return 'active'; // Default
    }

    /**
     * Convert date format
     */
    private function convertDate($date)
    {
        if (is_numeric($date)) {
            // Excel date format
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
        }

        // Try to parse common date formats
        try {
            return date('Y-m-d', strtotime($date));
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }
}
