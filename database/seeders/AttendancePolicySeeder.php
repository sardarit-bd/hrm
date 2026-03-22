<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendancePolicy;

class AttendancePolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'name'                          => 'Standard Policy',
                'grace_period_minutes'          => 10,
                'late_count_threshold'          => 5,
                'late_threshold_deduction_days' => 1.00,
                'absent_deduction_per_day'      => 1.00,
                'half_day_threshold_hours'      => 4.00,
                'effective_from'                => '2024-01-01',
                'effective_to'                  => null,
                'created_by'                    => 1,
            ],
            [
                'name'                          => 'Strict Policy',
                'grace_period_minutes'          => 5,
                'late_count_threshold'          => 5,
                'late_threshold_deduction_days' => 1.00,
                'absent_deduction_per_day'      => 1.00,
                'half_day_threshold_hours'      => 4.00,
                'effective_from'                => '2024-01-01',
                'effective_to'                  => null,
                'created_by'                    => 1,
            ],
            [
                'name'                          => 'Relaxed Policy',
                'grace_period_minutes'          => 15,
                'late_count_threshold'          => 20,
                'late_threshold_deduction_days' => 1.00,
                'absent_deduction_per_day'      => 1.00,
                'half_day_threshold_hours'      => 4.00,
                'effective_from'                => '2024-01-01',
                'effective_to'                  => null,
                'created_by'                    => 1,
            ],
        ];

        foreach ($policies as $policy) {
            AttendancePolicy::firstOrCreate(
                ['name' => $policy['name']],
                $policy
            );
        }
    }
}