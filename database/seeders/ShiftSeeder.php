<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name'          => 'Day Shift',
                'start_time'    => '09:00:00',
                'end_time'      => '18:00:00',
                'cross_midnight'=> false,
                'working_hours' => 9.00,
                'is_fixed'      => true,
            ],
            [
                'name'          => 'Morning Shift',
                'start_time'    => '06:00:00',
                'end_time'      => '14:00:00',
                'cross_midnight'=> false,
                'working_hours' => 8.00,
                'is_fixed'      => false,
            ],
            [
                'name'          => 'Evening Shift',
                'start_time'    => '14:00:00',
                'end_time'      => '22:00:00',
                'cross_midnight'=> false,
                'working_hours' => 8.00,
                'is_fixed'      => false,
            ],
            [
                'name'          => 'Night Shift',
                'start_time'    => '22:00:00',
                'end_time'      => '06:00:00',
                'cross_midnight'=> true,
                'working_hours' => 8.00,
                'is_fixed'      => false,
            ],
            [
                'name'          => 'Half Day Shift',
                'start_time'    => '09:00:00',
                'end_time'      => '13:00:00',
                'cross_midnight'=> false,
                'working_hours' => 4.00,
                'is_fixed'      => false,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(
                ['name' => $shift['name']],
                $shift
            );
        }
    }
}