<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeePolicyAssignment;
use App\Models\User;
use App\Models\AttendancePolicy;

class EmployeePolicyAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin          = User::where('role', 'super_admin')->first();
        $standardPolicy = AttendancePolicy::where('name', 'Standard Policy')->first();

        $users = User::where('status', 'active')->get();

        foreach ($users as $user) {
            EmployeePolicyAssignment::firstOrCreate(
                [
                    'user_id'      => $user->id,
                    'effective_to' => null,
                ],
                [
                    'attendance_policy_id' => $standardPolicy->id,
                    'effective_from'       => $user->joining_date,
                    'effective_to'         => null,
                    'assigned_by'          => $admin->id,
                ]
            );
        }
    }
}