<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeSalary;
use App\Models\User;

class EmployeeSalarySeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::role('super_admin')->first();
        $gm         = User::role('general_manager')->first();
        $hr         = User::role('hr')->first();
        $pm         = User::role('project_manager')->first();
        $tl         = User::role('team_leader')->first();
        $employee   = User::role('employee')->first();

        // Super Admin creates all salaries
        $createdBy = $superAdmin?->id ?? 1;

        $salaries = [
            [
                'user_id'        => $superAdmin?->id,
                'basic_salary'   => 150000,
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'created_by'     => $createdBy,
            ],
            [
                'user_id'        => $gm?->id,
                'basic_salary'   => 120000,
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'created_by'     => $createdBy,
            ],
            [
                'user_id'        => $hr?->id,
                'basic_salary'   => 80000,
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'created_by'     => $createdBy,
            ],
            [
                'user_id'        => $pm?->id,
                'basic_salary'   => 100000,
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'created_by'     => $createdBy,
            ],
            [
                'user_id'        => $tl?->id,
                'basic_salary'   => 80000,
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'created_by'     => $createdBy,
            ],
            [
                'user_id'        => $employee?->id,
                'basic_salary'   => 60000,
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'created_by'     => $createdBy,
            ],
        ];

        foreach ($salaries as $salary) {
            if (!$salary['user_id']) continue;

            EmployeeSalary::firstOrCreate(
                [
                    'user_id'        => $salary['user_id'],
                    'effective_from' => $salary['effective_from'],
                ],
                $salary
            );
        }
    }
}