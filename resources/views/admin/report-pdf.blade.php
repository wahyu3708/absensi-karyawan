<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Rekap Absensi - {{ $monthName }} {{ $year }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            padding: 30px 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: normal;
            color: #555;
        }

        .meta {
            margin-bottom: 15px;
            font-size: 10px;
            color: #666;
        }

        .meta span {
            margin-right: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #4338CA;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            font-size: 10px;
        }

        tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        tbody tr:hover {
            background-color: #eef2ff;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mono {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9px;
        }

        .rate-good {
            color: #16a34a;
            font-weight: bold;
        }

        .rate-warn {
            color: #d97706;
            font-weight: bold;
        }

        .rate-bad {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ccc;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }

        .summary-box {
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #f8fafc;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 10px;
        }

        .summary-box strong {
            color: #4338CA;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Rekap Absensi Karyawan</h1>
        <h2>Periode: {{ $monthName }} {{ $year }}</h2>
    </div>

    <div class="summary-box">
        <strong>Total Karyawan:</strong> {{ count($reportData) }} orang &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Dicetak:</strong> {{ now()->translatedFormat('l, d F Y H:i') }} WIB
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th style="width: 70px;">NIP</th>
                <th>Nama</th>
                <th>Departemen</th>
                <th>Shift</th>
                <th class="text-center" style="width: 45px;">Hadir</th>
                <th class="text-center" style="width: 55px;">Tepat Waktu</th>
                <th class="text-center" style="width: 55px;">Terlambat</th>
                <th class="text-center" style="width: 55px;">Plg Awal</th>
                <th class="text-center" style="width: 50px;">Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="mono">{{ $row['employee']->employee_id }}</td>
                    <td>{{ $row['employee']->name }}</td>
                    <td>{{ $row['employee']->department }}</td>
                    <td>{{ $row['employee']->shift?->name ?? '-' }}</td>
                    <td class="text-center">{{ $row['total_present'] }}</td>
                    <td class="text-center">{{ $row['total_on_time'] }}</td>
                    <td class="text-center">{{ $row['total_late'] }}</td>
                    <td class="text-center">{{ $row['total_early_leave'] }}</td>
                    <td class="text-center {{ $row['attendance_rate'] >= 90 ? 'rate-good' : ($row['attendance_rate'] >= 75 ? 'rate-warn' : 'rate-bad') }}">
                        {{ $row['attendance_rate'] }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <span class="footer-left">Rekap Absensi — {{ $monthName }} {{ $year }}</span>
        <span class="footer-right">Dicetak pada {{ now()->translatedFormat('d/m/Y H:i') }}</span>
    </div>
</body>

</html>
