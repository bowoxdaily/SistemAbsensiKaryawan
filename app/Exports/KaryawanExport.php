<?php

namespace App\Exports;

use App\Models\Karyawans;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KaryawanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Karyawans::with(['department', 'position'])
            ->orderBy('employee_code', 'asc')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Karyawan',
            'NIK',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Status Perkawinan',
            'Departemen',
            'Posisi',
            'Tanggal Bergabung',
            'Status Kerja',
            'Jenis Shift',
            'Status',
            'Alamat',
            'Kota',
            'Provinsi',
            'Kode Pos',
            'No. HP',
            'Email',
            'Kontak Darurat (Nama)',
            'Kontak Darurat (No)',
        ];
    }

    /**
     * @param mixed $karyawan
     * @return array
     */
    public function map($karyawan): array
    {
        return [
            $karyawan->employee_code,
            $karyawan->nik ?? '-',
            $karyawan->name,
            $karyawan->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            $karyawan->birth_place,
            $karyawan->birth_date,
            $karyawan->marital_status,
            $karyawan->department ? $karyawan->department->name : '-',
            $karyawan->position ? $karyawan->position->name : '-',
            $karyawan->join_date,
            $karyawan->employment_status,
            $karyawan->shift_type,
            $this->getStatusLabel($karyawan->status),
            $karyawan->address,
            $karyawan->city,
            $karyawan->province,
            $karyawan->postal_code,
            $karyawan->phone,
            $karyawan->email,
            $karyawan->emergency_contact_name,
            $karyawan->emergency_contact_phone,
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Kode
            'B' => 18,  // NIK
            'C' => 25,  // Nama
            'D' => 15,  // Jenis Kelamin
            'E' => 20,  // Tempat Lahir
            'F' => 15,  // Tanggal Lahir
            'G' => 18,  // Status Perkawinan
            'H' => 20,  // Departemen
            'I' => 20,  // Posisi
            'J' => 18,  // Tanggal Bergabung
            'K' => 15,  // Status Kerja
            'L' => 12,  // Jenis Shift
            'M' => 12,  // Status
            'N' => 35,  // Alamat
            'O' => 15,  // Kota
            'P' => 15,  // Provinsi
            'Q' => 12,  // Kode Pos
            'R' => 15,  // No HP
            'S' => 25,  // Email
            'T' => 25,  // Kontak Darurat Nama
            'U' => 15,  // Kontak Darurat No
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E9']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Get status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'resign' => 'Resign'
        ];
        return $labels[$status] ?? $status;
    }
}
