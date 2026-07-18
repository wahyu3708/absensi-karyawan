<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('role', 'employee')->with('shift')->get();
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->subDay();

        foreach ($employees as $employee) {
            $date = $startDate->copy();

            while ($date->lte($endDate)) {
                // Skip Sundays
                if ($date->isSunday()) {
                    $date->addDay();
                    continue;
                }

                // 90% chance of attendance
                if (rand(1, 100) <= 90) {
                    $shiftStart = Carbon::parse($employee->shift->getRawOriginal('start_time'));
                    $shiftEnd = Carbon::parse($employee->shift->getRawOriginal('end_time'));

                    // Randomize clock in: 80% on time, 15% late, 5% very late
                    $rand = rand(1, 100);
                    if ($rand <= 80) {
                        // On time: 0-5 min before or after shift start
                        $minuteOffset = rand(-10, 5);
                        $status = $minuteOffset > 10 ? 'late' : 'on_time';
                    } elseif ($rand <= 95) {
                        // Late: 11-25 minutes
                        $minuteOffset = rand(11, 25);
                        $status = 'late';
                    } else {
                        // Very late: 31-60 minutes
                        $minuteOffset = rand(31, 60);
                        $status = 'very_late';
                    }

                    $clockIn = $date->copy()
                        ->setTimeFromTimeString($shiftStart->format('H:i:s'))
                        ->addMinutes($minuteOffset);

                    // Clock out: 85% on time, 15% early leave
                    $clockOutRand = rand(1, 100);
                    if ($clockOutRand <= 85) {
                        $clockOutOffset = rand(0, 15);
                        $clockOutStatus = 'on_time';
                    } else {
                        $clockOutOffset = rand(-45, -10);
                        $clockOutStatus = 'early_leave';
                    }

                    $clockOut = $date->copy()
                        ->setTimeFromTimeString($shiftEnd->format('H:i:s'))
                        ->addMinutes($clockOutOffset);

                    // Re-determine status based on 10 min tolerance
                    $actualStatus = 'on_time';
                    if ($minuteOffset > 10 && $minuteOffset <= 30) {
                        $actualStatus = 'late';
                    } elseif ($minuteOffset > 30) {
                        $actualStatus = 'very_late';
                    }

                    Attendance::create([
                        'user_id' => $employee->id,
                        'date' => $date->format('Y-m-d'),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'clock_in_status' => $actualStatus,
                        'clock_out_status' => $clockOutStatus,
                        'shift_id' => $employee->shift_id,
                        'notes' => null,
                        'ip_address' => '192.168.1.' . rand(1, 254),
                        'latitude' => -6.200000 + (rand(-50, 50) / 100000),
                        'longitude' => 106.816666 + (rand(-50, 50) / 100000),
                        'location_valid' => true,
                    ]);
                }

                $date->addDay();
            }
        }
    }
}
