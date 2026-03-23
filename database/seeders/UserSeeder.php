<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'employee_code' => 'EMP-0001',
                'full_name'     => 'Super Admin',
                'email'         => 'superadmin@company.com',
                'password'      => Hash::make('password'),
                'department_id' => 1,
                'designation'   => 'Super Administrator',
                'phone'         => '01700000001',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
                'role'          => 'super_admin',
            ],
            [
                'employee_code' => 'EMP-0002',
                'full_name'     => 'General Manager',
                'email'         => 'gm@company.com',
                'password'      => Hash::make('password'),
                'department_id' => 1,
                'designation'   => 'General Manager',
                'phone'         => '01700000002',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
                'role'          => 'general_manager',
            ],
            [
                'employee_code' => 'EMP-0003',
                'full_name'     => 'HR Manager',
                'email'         => 'hr@company.com',
                'password'      => Hash::make('password'),
                'department_id' => 3,
                'designation'   => 'HR Manager',
                'phone'         => '01700000003',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
                'role'          => 'hr',
            ],
            [
                'employee_code' => 'EMP-0004',
                'full_name'     => 'Project Manager',
                'email'         => 'pm@company.com',
                'password'      => Hash::make('password'),
                'department_id' => 2,
                'designation'   => 'Project Manager',
                'phone'         => '01700000004',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
                'role'          => 'project_manager',
            ],
            [
                'employee_code' => 'EMP-0005',
                'full_name'     => 'Team Leader',
                'email'         => 'tl@company.com',
                'password'      => Hash::make('password'),
                'department_id' => 2,
                'designation'   => 'Team Leader',
                'phone'         => '01700000005',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
                'role'          => 'team_leader',
            ],
            [
                'employee_code' => 'EMP-0006',
                'full_name'     => 'John Developer',
                'email'         => 'john@company.com',
                'password'      => Hash::make('password'),
                'department_id' => 2,
                'designation'   => 'Senior Developer',
                'phone'         => '01700000006',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
                'role'          => 'employee',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(
                ['employee_code' => $userData['employee_code']],
                $userData
            );

            $user->syncRoles([$role]);
        }
    }
}