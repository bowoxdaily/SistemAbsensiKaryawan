<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\Position;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class KaryawanTemplateExport implements WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $departments;
    protected $positions;

    public function __construct()
    {
        // Get all departments and active positions
        $this->departments = Department::orderBy('name')
            ->pluck('name')
            ->toArray();

        $this->positions = Position::where('status', 'active')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
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
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 18,
            'C' => 25,
            'D' => 15,
            'E' => 20,
            'F' => 15,
            'G' => 18,
            'H' => 20,
            'I' => 20,
            'J' => 18,
            'K' => 15,
            'L' => 12,
            'M' => 12,
            'N' => 35,
            'O' => 15,
            'P' => 15,
            'Q' => 12,
            'R' => 15,
            'S' => 25,
            'T' => 25,
            'U' => 15,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Add example data in row 2
        $sheet->setCellValue('A2', 'EMP001');
        $sheet->setCellValue('B2', '1234567890123456');
        $sheet->setCellValue('C2', 'John Doe');
        $sheet->setCellValue('D2', 'Laki-laki');
        $sheet->setCellValue('E2', 'Jakarta');
        $sheet->setCellValue('F2', '1990-01-15');
        $sheet->setCellValue('G2', 'Menikah');
        $sheet->setCellValue('H2', 'IT');
        $sheet->setCellValue('I2', 'Staff');
        $sheet->setCellValue('J2', '2020-01-01');
        $sheet->setCellValue('K2', 'Tetap');
        $sheet->setCellValue('L2', 'Pagi');
        $sheet->setCellValue('M2', 'Aktif');
        $sheet->setCellValue('N2', 'Jl. Contoh No. 123');
        $sheet->setCellValue('O2', 'Jakarta Selatan');
        $sheet->setCellValue('P2', 'DKI Jakarta');
        $sheet->setCellValue('Q2', '12345');
        $sheet->setCellValue('R2', '081234567890');
        $sheet->setCellValue('S2', 'john.doe@example.com');
        $sheet->setCellValue('T2', 'Jane Doe');
        $sheet->setCellValue('U2', '081234567891');

        // Add notes in row 3
        $sheet->setCellValue('A3', 'Contoh: EMP002');
        $sheet->setCellValue('D3', 'Pilih dari dropdown ⬇');
        $sheet->setCellValue('F3', 'Format: YYYY-MM-DD');
        $sheet->setCellValue('G3', 'Pilih dari dropdown ⬇');
        $sheet->setCellValue('H3', 'Pilih dari dropdown ⬇');
        $sheet->setCellValue('I3', 'Pilih dari dropdown ⬇');
        $sheet->setCellValue('J3', 'Format: YYYY-MM-DD');
        $sheet->setCellValue('K3', 'Pilih dari dropdown ⬇');
        $sheet->setCellValue('L3', 'Pilih dari dropdown ⬇');
        $sheet->setCellValue('M3', 'Pilih dari dropdown ⬇');
        $sheet->setCellValue('S3', 'Harus unique');

        return [
            // Style header row
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
            // Style example row
            2 => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E9']
                ],
            ],
            // Style notes row
            3 => [
                'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '666666']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF9C4']
                ],
            ],
        ];
    }

    /**
     * Register events untuk menambahkan data validation (dropdown)
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set dropdown untuk Jenis Kelamin (Column D) - dari row 2 sampai 1000
                $genderValidation = $sheet->getCell('D2')->getDataValidation();
                $genderValidation->setType(DataValidation::TYPE_LIST);
                $genderValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $genderValidation->setAllowBlank(false);
                $genderValidation->setShowInputMessage(true);
                $genderValidation->setShowErrorMessage(true);
                $genderValidation->setShowDropDown(true);
                $genderValidation->setErrorTitle('Input error');
                $genderValidation->setError('Pilih dari dropdown');
                $genderValidation->setPromptTitle('Jenis Kelamin');
                $genderValidation->setPrompt('Pilih Laki-laki atau Perempuan');
                $genderValidation->setFormula1('"Laki-laki,Perempuan"');

                // Copy validation ke row lainnya
                for ($i = 2; $i <= 1000; $i++) {
                    $sheet->getCell('D' . $i)->setDataValidation(clone $genderValidation);
                }

                // Set dropdown untuk Status Perkawinan (Column G)
                $maritalValidation = $sheet->getCell('G2')->getDataValidation();
                $maritalValidation->setType(DataValidation::TYPE_LIST);
                $maritalValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $maritalValidation->setAllowBlank(false);
                $maritalValidation->setShowInputMessage(true);
                $maritalValidation->setShowErrorMessage(true);
                $maritalValidation->setShowDropDown(true);
                $maritalValidation->setErrorTitle('Input error');
                $maritalValidation->setError('Pilih dari dropdown');
                $maritalValidation->setPromptTitle('Status Perkawinan');
                $maritalValidation->setPrompt('Pilih status perkawinan');
                $maritalValidation->setFormula1('"Belum Menikah,Menikah,Duda,Janda"');

                for ($i = 2; $i <= 1000; $i++) {
                    $sheet->getCell('G' . $i)->setDataValidation(clone $maritalValidation);
                }

                // Set dropdown untuk Departemen (Column H) - dari database
                if (count($this->departments) > 0) {
                    $departmentList = '"' . implode(',', $this->departments) . '"';
                    $deptValidation = $sheet->getCell('H2')->getDataValidation();
                    $deptValidation->setType(DataValidation::TYPE_LIST);
                    $deptValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $deptValidation->setAllowBlank(false);
                    $deptValidation->setShowInputMessage(true);
                    $deptValidation->setShowErrorMessage(true);
                    $deptValidation->setShowDropDown(true);
                    $deptValidation->setErrorTitle('Input error');
                    $deptValidation->setError('Pilih departemen yang valid dari dropdown');
                    $deptValidation->setPromptTitle('Departemen');
                    $deptValidation->setPrompt('Pilih departemen yang sudah terdaftar');
                    $deptValidation->setFormula1($departmentList);

                    for ($i = 2; $i <= 1000; $i++) {
                        $sheet->getCell('H' . $i)->setDataValidation(clone $deptValidation);
                    }
                }

                // Set dropdown untuk Posisi/Jabatan (Column I) - dari database
                if (count($this->positions) > 0) {
                    $positionList = '"' . implode(',', $this->positions) . '"';
                    $posValidation = $sheet->getCell('I2')->getDataValidation();
                    $posValidation->setType(DataValidation::TYPE_LIST);
                    $posValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $posValidation->setAllowBlank(false);
                    $posValidation->setShowInputMessage(true);
                    $posValidation->setShowErrorMessage(true);
                    $posValidation->setShowDropDown(true);
                    $posValidation->setErrorTitle('Input error');
                    $posValidation->setError('Pilih posisi yang valid dari dropdown');
                    $posValidation->setPromptTitle('Posisi/Jabatan');
                    $posValidation->setPrompt('Pilih posisi yang sudah terdaftar');
                    $posValidation->setFormula1($positionList);

                    for ($i = 2; $i <= 1000; $i++) {
                        $sheet->getCell('I' . $i)->setDataValidation(clone $posValidation);
                    }
                }

                // Set dropdown untuk Status Kerja (Column K)
                $empStatusValidation = $sheet->getCell('K2')->getDataValidation();
                $empStatusValidation->setType(DataValidation::TYPE_LIST);
                $empStatusValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $empStatusValidation->setAllowBlank(false);
                $empStatusValidation->setShowInputMessage(true);
                $empStatusValidation->setShowErrorMessage(true);
                $empStatusValidation->setShowDropDown(true);
                $empStatusValidation->setErrorTitle('Input error');
                $empStatusValidation->setError('Pilih dari dropdown');
                $empStatusValidation->setPromptTitle('Status Kerja');
                $empStatusValidation->setPrompt('Pilih status kerja karyawan');
                $empStatusValidation->setFormula1('"Tetap,Kontrak,Magang,Outsource"');

                for ($i = 2; $i <= 1000; $i++) {
                    $sheet->getCell('K' . $i)->setDataValidation(clone $empStatusValidation);
                }

                // Set dropdown untuk Jenis Shift (Column L)
                $shiftValidation = $sheet->getCell('L2')->getDataValidation();
                $shiftValidation->setType(DataValidation::TYPE_LIST);
                $shiftValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $shiftValidation->setAllowBlank(false);
                $shiftValidation->setShowInputMessage(true);
                $shiftValidation->setShowErrorMessage(true);
                $shiftValidation->setShowDropDown(true);
                $shiftValidation->setErrorTitle('Input error');
                $shiftValidation->setError('Pilih dari dropdown');
                $shiftValidation->setPromptTitle('Jenis Shift');
                $shiftValidation->setPrompt('Pilih jenis shift kerja');
                $shiftValidation->setFormula1('"Pagi,Sore,Malam,Rotasi"');

                for ($i = 2; $i <= 1000; $i++) {
                    $sheet->getCell('L' . $i)->setDataValidation(clone $shiftValidation);
                }

                // Set dropdown untuk Status Karyawan (Column M)
                $statusValidation = $sheet->getCell('M2')->getDataValidation();
                $statusValidation->setType(DataValidation::TYPE_LIST);
                $statusValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $statusValidation->setAllowBlank(false);
                $statusValidation->setShowInputMessage(true);
                $statusValidation->setShowErrorMessage(true);
                $statusValidation->setShowDropDown(true);
                $statusValidation->setErrorTitle('Input error');
                $statusValidation->setError('Pilih dari dropdown');
                $statusValidation->setPromptTitle('Status Karyawan');
                $statusValidation->setPrompt('Pilih status aktif karyawan');
                $statusValidation->setFormula1('"Aktif,Tidak Aktif,Resign"');

                for ($i = 2; $i <= 1000; $i++) {
                    $sheet->getCell('M' . $i)->setDataValidation(clone $statusValidation);
                }
            },
        ];
    }
}
