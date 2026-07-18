<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected array $reportData;
    protected string $monthName;
    protected int $year;

    public function __construct(array $reportData, string $monthName, int $year)
    {
        $this->reportData = $reportData;
        $this->monthName = $monthName;
        $this->year = $year;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama',
            'Departemen',
            'Shift',
            'Hadir',
            'Tepat Waktu',
            'Terlambat',
            'Pulang Awal',
            'Rate (%)',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->reportData as $i => $row) {
            $rows[] = [
                $i + 1,
                $row['employee']->employee_id,
                $row['employee']->name,
                $row['employee']->department,
                $row['employee']->shift?->name ?? '-',
                $row['total_present'],
                $row['total_on_time'],
                $row['total_late'],
                $row['total_early_leave'],
                $row['attendance_rate'],
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return "Rekap {$this->monthName} {$this->year}";
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->reportData) + 1;

        // Apply borders to entire data range
        $sheet->getStyle("A1:J{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Center align numeric columns (F-J)
        $sheet->getStyle("F2:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4338CA'], // Indigo-700
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
