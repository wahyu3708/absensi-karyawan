<?php

namespace Database\Seeders;

use App\Models\CompanyLocation;
use Illuminate\Database\Seeder;

class CompanyLocationSeeder extends Seeder
{
    public function run(): void
    {
        CompanyLocation::firstOrCreate(
            ['is_active' => true],
            [
                'name' => 'Toko Takasimura Wangkal',
                'address' => 'Wangkal, Gading, Probolinggo',
                'latitude' => -7.842498796390817,
                'longitude' => 113.44212290165065,
                'radius' => 50,
                'is_active' => true,
            ]
        );
    }
}
