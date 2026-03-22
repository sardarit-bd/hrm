<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RosterAssignment;
use App\Models\User;
use App\Models\Shift;

class RosterAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin    = User::where('role', 'super_admin')->first();
        $dayShift = Shift::where('name', 'Day Shift')->first();

        $users = User::where('status', 'active')->get();

        foreach ($users as $user) {
            RosterAssignment::firstOrCreate(
                [
                    'user_id'      => $user->id,
                    'effective_to' => null,
                ],
                [
                    'shift_id'       => $dayShift->id,
                    'weekend_days'   => ['friday'],
                    'effective_from' => $user->joining_date,
                    'effective_to'   => null,
                    'assigned_by'    => $admin->id,
                ]
            );
        }
    }
}