<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name'               => 'Sick Leave',
                'max_days_per_year'  => 14,
                'is_paid'            => true,
            ],
            [
                'name'               => 'Casual Leave',
                'max_days_per_year'  => 10,
                'is_paid'            => true,
            ],
            [
                'name'               => 'Earned Leave',
                'max_days_per_year'  => 18,
                'is_paid'            => true,
            ],
            [
                'name'               => 'Unpaid Leave',
                'max_days_per_year'  => 30,
                'is_paid'            => false,
            ],
            [
                'name'               => 'Maternity Leave',
                'max_days_per_year'  => 90,
                'is_paid'            => true,
            ],
            [
                'name'               => 'Paternity Leave',
                'max_days_per_year'  => 7,
                'is_paid'            => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['name' => $leaveType['name']],
                $leaveType
            );
        }
    }
}