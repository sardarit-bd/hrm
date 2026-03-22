<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            [
                'name'         => 'New Year\'s Day',
                'date'         => '2026-01-01',
                'is_recurring' => true,
            ],
            [
                'name'         => 'International Workers Day',
                'date'         => '2026-05-01',
                'is_recurring' => true,
            ],
            [
                'name'         => 'Independence Day',
                'date'         => '2026-03-26',
                'is_recurring' => true,
            ],
            [
                'name'         => 'Victory Day',
                'date'         => '2026-12-16',
                'is_recurring' => true,
            ],
            [
                'name'         => 'Eid ul-Fitr',
                'date'         => '2026-03-31',
                'is_recurring' => false,
            ],
            [
                'name'         => 'Eid ul-Adha',
                'date'         => '2026-06-07',
                'is_recurring' => false,
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                $holiday
            );
        }
    }
}