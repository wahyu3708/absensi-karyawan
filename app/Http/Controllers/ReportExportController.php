<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceReportExport;
use App\Models\Attendance;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    /**
     * Build report data for a given month/year.
     */
    private function buildReportData(int $month, int $year): array
    {
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->with('shift')
            ->orderBy('employee_id')
            ->get();

        $reportData = [];
        foreach ($employees as $employee) {
            $attendances = Attendance::where('user_id', $employee->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();

            $reportData[] = [
                'employee' => $employee,
                'total_present' => $attendances->whereNotNull('clock_in')->count(),
                'total_late' => $attendances->whereIn('clock_in_status', ['late', 'very_late'])->count(),
                'total_on_time' => $attendances->where('clock_in_status', 'on_time')->count(),
                'total_early_leave' => $attendances->where('clock_out_status', 'early_leave')->count(),
                'attendance_rate' => $employee->attendanceRate($year, $month),
            ];
        }

        return $reportData;
    }

    /**
     * Export attendance report as PDF.
     */
    public function exportPdf(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $reportData = $this->buildReportData($month, $year);
        $monthName = Carbon::create($year, $month)->translatedFormat('F');

        $pdf = Pdf::loadView('admin.report-pdf', compact('reportData', 'monthName', 'year'))
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true);

        $filename = "rekap-absensi-{$monthName}-{$year}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export attendance report as Excel.
     */
    public function exportExcel(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $reportData = $this->buildReportData($month, $year);
        $monthName = Carbon::create($year, $month)->translatedFormat('F');

        $filename = "rekap-absensi-{$monthName}-{$year}.xlsx";

        return Excel::download(
            new AttendanceReportExport($reportData, $monthName, $year),
            $filename
        );
    }
}
