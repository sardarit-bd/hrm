<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeSalary;
use App\Models\User;

class EmployeeSalarySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'super_admin')->first();

        $salaries = [
            'superadmin@sardarit.com' => 80000.00,
            'gm@sardarit.com'         => 70000.00,
            'pm@sardarit.com'         => 60000.00,
            'tl@sardarit.com'         => 50000.00,
            'john@sardarit.com'       => 45000.00,
            'jane@sardarit.com'       => 35000.00,
        ];

        foreach ($salaries as $email => $salary) {
            $user = User::where('email', $email)->first();

            if ($user) {
                EmployeeSalary::firstOrCreate(
                    [
                        'user_id'      => $user->id,
                        'effective_to' => null,
                    ],
                    [
                        'basic_salary'   => $salary,
                        'effective_from' => $user->joining_date,
                        'effective_to'   => null,
                        'created_by'     => $admin->id,
                    ]
                );
            }
        }
    }
}