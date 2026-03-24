<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            ChannelSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            ShiftSeeder::class,
            AttendancePolicySeeder::class,
            LeaveTypeSeeder::class,
            HolidaySeeder::class,
            EmployeeSalarySeeder::class,
            RosterAssignmentSeeder::class,
            EmployeePolicyAssignmentSeeder::class,
        ]);
    }
}