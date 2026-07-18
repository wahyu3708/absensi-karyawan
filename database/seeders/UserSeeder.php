<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@absensi.com',
            'password' => Hash::make('admin123'),
            'employee_id' => 'ADM-001',
            'department' => 'Management',
            'position' => 'System Administrator',
            'role' => 'admin',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        // Department distribution for 32 employees
        $departments = [
            'Manajer' => ['Manajer Toko', 'Wakil Manajer', 'Asisten Manajer'],
            'HRD' => ['HR Manager', 'HR Staff', 'Recruiter', 'Pelatihan'],
            'Supervisor' => ['Kepala Shift', 'Wakil Kepala Shift'],
            'Marketing' => ['Marketing Manager', 'Digital Marketing', 'Sales Executive', 'Brand Officer'],
            'Keuangan' => ['Kasir', 'Staff Keuangan', 'Staff Audit'],
            'Operasi' => ['Manajer Operasi', 'Staf Logistik', 'Staf Gudang', 'Kontrol Kualitas'],
            'Umum' => ['Staf GA', 'Resepsionis', 'Satpam'],
            'Customer Service' => ['CS Manager', 'CS Staff', 'Help Desk', 'Complaint Handler'],
        ];

        $employeeNames = [
            'Ahmad Fauzi', 'Budi Santoso', 'Citra Dewi', 'Dian Permata',
            'Eka Saputra', 'Fitri Handayani', 'Gilang Pratama', 'Hesti Rahayu',
            'Irwan Setiawan', 'Joko Widodo', 'Kartika Sari', 'Lukman Hakim',
            'Maya Anggraini', 'Nurul Hidayah', 'Oscar Gunawan', 'Putri Wulandari',
            'Qori Aisyah', 'Rizky Aditya', 'Sinta Maharani', 'Taufik Hidayat',
            'Umar Farouq', 'Vina Octavia', 'Wahyu Nugroho', 'Xena Paramita',
            'Yusuf Ibrahim', 'Zahra Amelia', 'Arief Rahman', 'Bella Safitri',
            'Cahyo Purnomo', 'Dewi Lestari', 'Endra Wijaya', 'Farah Diba',
        ];

        $employeeId = 1;
        $nameIndex = 0;

        foreach ($departments as $department => $positions) {
            foreach ($positions as $position) {
                if ($nameIndex >= 32) break;

                // Alternate shifts: first 16 = shift 1, next 16 = shift 2
                $shiftId = $nameIndex < 16 ? 1 : 2;

                User::create([
                    'name' => $employeeNames[$nameIndex],
                    'email' => strtolower(str_replace(' ', '.', $employeeNames[$nameIndex])) . '@absensi.com',
                    'password' => Hash::make('password123'),
                    'employee_id' => 'EMP-' . str_pad($employeeId, 3, '0', STR_PAD_LEFT),
                    'department' => $department,
                    'position' => $position,
                    'shift_id' => $shiftId,
                    'role' => 'employee',
                    'phone' => '08' . rand(1000000000, 9999999999),
                    'is_active' => true,
                ]);

                $employeeId++;
                $nameIndex++;
            }
        }
    }
}
