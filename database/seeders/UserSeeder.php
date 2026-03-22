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
                'email'         => 'superadmin@sardarit.com',
                'password'      => Hash::make('password'),
                'role'          => 'super_admin',
                'department'    => 'Management',
                'designation'   => 'Super Administrator',
                'phone'         => '01700000001',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
            ],
            [
                'employee_code' => 'EMP-0002',
                'full_name'     => 'General Manager',
                'email'         => 'gm@sardarit.com',
                'password'      => Hash::make('password'),
                'role'          => 'general_manager',
                'department'    => 'Management',
                'designation'   => 'General Manager',
                'phone'         => '01700000002',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
            ],
            [
                'employee_code' => 'EMP-0003',
                'full_name'     => 'Project Manager',
                'email'         => 'pm@sardarit.com',
                'password'      => Hash::make('password'),
                'role'          => 'project_manager',
                'department'    => 'Engineering',
                'designation'   => 'Project Manager',
                'phone'         => '01700000003',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
            ],
            [
                'employee_code' => 'EMP-0004',
                'full_name'     => 'Team Leader',
                'email'         => 'tl@sardarit.com',
                'password'      => Hash::make('password'),
                'role'          => 'team_leader',
                'department'    => 'Engineering',
                'designation'   => 'Team Leader',
                'phone'         => '01700000004',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
            ],
            [
                'employee_code' => 'EMP-0005',
                'full_name'     => 'John Developer',
                'email'         => 'john@sardarit.com',
                'password'      => Hash::make('password'),
                'role'          => 'employee',
                'department'    => 'Engineering',
                'designation'   => 'Senior Developer',
                'phone'         => '01700000005',
                'joining_date'  => '2024-01-01',
                'status'        => 'active',
            ],
            [
                'employee_code' => 'EMP-0006',
                'full_name'     => 'Jane Developer',
                'email'         => 'jane@sardarit.com',
                'password'      => Hash::make('password'),
                'role'          => 'employee',
                'department'    => 'Engineering',
                'designation'   => 'Junior Developer',
                'phone'         => '01700000006',
                'joining_date'  => '2024-02-01',
                'status'        => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}