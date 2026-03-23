<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // =================== Create Permissions ===================
        $permissions = [
            // Departments
            'departments.view',
            'departments.create',
            'departments.update',
            'departments.delete',

            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.change-status',

            // Shifts
            'shifts.view',
            'shifts.create',
            'shifts.update',
            'shifts.delete',

            // Roster
            'roster.view',
            'roster.assign',

            // Attendance Policy
            'attendance-policy.view',
            'attendance-policy.create',
            'attendance-policy.update',
            'attendance-policy.delete',
            'attendance-policy.assign',

            // Leave Types
            'leave-type.view',
            'leave-type.create',
            'leave-type.update',
            'leave-type.delete',

            // Leave Requests
            'leave-request.view',
            'leave-request.create',
            'leave-request.approve-pm',
            'leave-request.approve-gm',

            // Projects
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'projects.change-status',

            // Teams
            'teams.view',
            'teams.create',
            'teams.update',
            'teams.delete',
            'teams.assign-member',
            'teams.assign-project',
            'teams.assign-project-member',

            // Milestones
            'milestones.view',
            'milestones.create',
            'milestones.update',
            'milestones.delete',
            'milestones.complete',
            'milestones.missed',

            // Hour Logs
            'hour-logs.view',
            'hour-logs.create',
            'hour-logs.update',
            'hour-logs.delete',
            'hour-logs.approve',

            // Payroll
            'payroll.view',
            'payroll.generate',
            'payroll.approve',
            'payroll.pay',
            'payroll.update',

            // Feedback
            'feedback.view',
            'feedback.submit',

            // Holidays
            'holidays.view',
            'holidays.create',
            'holidays.update',
            'holidays.delete',

            // Notifications
            'notifications.view',

            // ZKTeco
            'zkteco.sync',

            // Roles & Permissions
            'roles.view',
            'roles.assign-permission',
            'roles.assign-user',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // =================== Create Roles ===================
        $superAdmin    = Role::firstOrCreate(['name' => 'super_admin']);
        $gm            = Role::firstOrCreate(['name' => 'general_manager']);
        $hr            = Role::firstOrCreate(['name' => 'hr']);
        $pm            = Role::firstOrCreate(['name' => 'project_manager']);
        $teamLeader    = Role::firstOrCreate(['name' => 'team_leader']);
        $employee      = Role::firstOrCreate(['name' => 'employee']);

        // =================== Assign Permissions ===================

        // Super Admin — all permissions
        $superAdmin->syncPermissions(Permission::all());

        // General Manager
        $gm->syncPermissions([
            'departments.view',
            'departments.create',
            'departments.update',
            'users.view',
            'users.create',
            'users.update',
            'users.change-status',
            'shifts.view',
            'shifts.create',
            'shifts.update',
            'shifts.delete',
            'roster.view',
            'roster.assign',
            'attendance-policy.view',
            'attendance-policy.create',
            'attendance-policy.update',
            'attendance-policy.assign',
            'leave-type.view',
            'leave-type.create',
            'leave-type.update',
            'leave-request.view',
            'leave-request.approve-gm',
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.change-status',
            'projects.delete',
            'teams.view',
            'teams.create',
            'teams.update',
            'teams.delete',
            'milestones.view',
            'milestones.create',
            'milestones.update',
            'milestones.complete',
            'milestones.missed',
            'hour-logs.view',
            'hour-logs.approve',
            'payroll.view',
            'payroll.generate',
            'payroll.approve',
            'payroll.pay',
            'payroll.update',
            'feedback.view',
            'feedback.submit',
            'holidays.view',
            'holidays.create',
            'holidays.update',
            'holidays.delete',
            'notifications.view',
            'roles.view',
        ]);

        // HR
        $hr->syncPermissions([
            'departments.view',
            'departments.create',
            'departments.update',
            'users.view',
            'users.create',
            'users.update',
            'users.change-status',
            'shifts.view',
            'shifts.create',
            'shifts.update',
            'shifts.delete',
            'roster.view',
            'roster.assign',
            'attendance-policy.view',
            'attendance-policy.create',
            'attendance-policy.update',
            'attendance-policy.delete',
            'attendance-policy.assign',
            'leave-type.view',
            'leave-type.create',
            'leave-type.update',
            'leave-type.delete',
            'leave-request.view',
            'payroll.view',
            'payroll.generate',
            'payroll.update',
            'feedback.view',
            'feedback.submit',
            'holidays.view',
            'holidays.create',
            'holidays.update',
            'holidays.delete',
            'notifications.view',
        ]);

        // Project Manager
        $pm->syncPermissions([
            'departments.view',
            'users.view',
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.change-status',
            'teams.view',
            'teams.create',
            'teams.update',
            'teams.assign-member',
            'teams.assign-project',
            'milestones.view',
            'milestones.create',
            'milestones.update',
            'milestones.complete',
            'milestones.missed',
            'hour-logs.view',
            'hour-logs.approve',
            'leave-request.view',
            'leave-request.approve-pm',
            'feedback.submit',
            'holidays.view',
            'notifications.view',
        ]);

        // Team Leader
        $teamLeader->syncPermissions([
            'departments.view',
            'projects.view',
            'teams.view',
            'teams.assign-project-member',
            'milestones.view',
            'hour-logs.view',
            'hour-logs.approve',
            'leave-request.view',
            'feedback.submit',
            'holidays.view',
            'notifications.view',
        ]);

        // Employee
        $employee->syncPermissions([
            'departments.view',
            'projects.view',
            'milestones.view',
            'hour-logs.create',
            'hour-logs.update',
            'hour-logs.delete',
            'leave-request.create',
            'feedback.submit',
            'holidays.view',
            'notifications.view',
            'payroll.view',
        ]);

        $this->command->info('Roles and permissions seeded successfully.');
    }
}