<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        Shift::insert([
            [
                'name' => 'Shift 1 (Pagi)',
                'start_time' => '07:00:00',
                'end_time' => '14:00:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift 2 (Siang)',
                'start_time' => '14:00:00',
                'end_time' => '21:00:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
