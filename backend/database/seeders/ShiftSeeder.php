<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'Shift Siang', 'start_time' => '08:00:00', 'end_time' => '20:00:00', 'status' => 'active'],
            ['name' => 'Shift Malam', 'start_time' => '20:00:00', 'end_time' => '08:00:00', 'status' => 'active'],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['name' => $shift['name']], $shift);
        }
    }
}
