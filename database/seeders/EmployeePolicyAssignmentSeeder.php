<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeePolicyAssignment;
use App\Models\User;

class EmployeePolicyAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::role('super_admin')->first();
        $gm         = User::role('general_manager')->first();
        $hr         = User::role('hr')->first();
        $pm         = User::role('project_manager')->first();
        $tl         = User::role('team_leader')->first();
        $employee   = User::role('employee')->first();

        $assignedBy = $superAdmin?->id ?? 1;

        $assignments = [
            $superAdmin?->id,
            $gm?->id,
            $hr?->id,
            $pm?->id,
            $tl?->id,
            $employee?->id,
        ];

        foreach ($assignments as $userId) {
            if (!$userId) continue;

            EmployeePolicyAssignment::firstOrCreate(
                [
                    'user_id'               => $userId,
                    'effective_from'        => '2024-01-01',
                ],
                [
                    'user_id'               => $userId,
                    'attendance_policy_id'  => 1,
                    'effective_from'        => '2024-01-01',
                    'effective_to'          => null,
                    'assigned_by'           => $assignedBy,
                ]
            );
        }
    }
}