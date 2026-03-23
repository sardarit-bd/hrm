<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RosterAssignment;
use App\Models\User;

class RosterAssignmentSeeder extends Seeder
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

        $rosters = [
            [
                'user_id'        => $superAdmin?->id,
                'shift_id'       => 1,
                'weekend_days'   => ['friday', 'saturday'],
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'assigned_by'    => $assignedBy,
            ],
            [
                'user_id'        => $gm?->id,
                'shift_id'       => 1,
                'weekend_days'   => ['friday', 'saturday'],
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'assigned_by'    => $assignedBy,
            ],
            [
                'user_id'        => $hr?->id,
                'shift_id'       => 1,
                'weekend_days'   => ['friday', 'saturday'],
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'assigned_by'    => $assignedBy,
            ],
            [
                'user_id'        => $pm?->id,
                'shift_id'       => 1,
                'weekend_days'   => ['friday', 'saturday'],
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'assigned_by'    => $assignedBy,
            ],
            [
                'user_id'        => $tl?->id,
                'shift_id'       => 2,
                'weekend_days'   => ['friday', 'saturday'],
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'assigned_by'    => $assignedBy,
            ],
            [
                'user_id'        => $employee?->id,
                'shift_id'       => 2,
                'weekend_days'   => ['friday', 'saturday'],
                'effective_from' => '2024-01-01',
                'effective_to'   => null,
                'assigned_by'    => $assignedBy,
            ],
        ];

        foreach ($rosters as $roster) {
            if (!$roster['user_id']) continue;

            RosterAssignment::firstOrCreate(
                [
                    'user_id'        => $roster['user_id'],
                    'effective_from' => $roster['effective_from'],
                ],
                $roster
            );
        }
    }
}