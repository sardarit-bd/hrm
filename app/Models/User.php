<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'employee_code',
        'full_name',
        'email',
        'password',
        'department_id',  // changed from department string
        'designation',
        'phone',
        'joining_date',
        'status',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    // =================== JWT ===================

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role'          => $this->getRoleNames()->first(),
            'employee_code' => $this->employee_code,
            'permissions'   => $this->getAllPermissions()->pluck('name'),
        ];
    }

    // =================== Relationships ===================
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function currentSalary()
    {
        return $this->hasOne(EmployeeSalary::class)
            ->whereNull('effective_to')
            ->latestOfMany('effective_from');
    }

    public function rosterAssignments()
    {
        return $this->hasMany(RosterAssignment::class);
    }

    public function currentRoster()
    {
        return $this->hasOne(RosterAssignment::class)
            ->whereNull('effective_to')
            ->latestOfMany('effective_from');
    }

    public function policyAssignments()
    {
        return $this->hasMany(EmployeePolicyAssignment::class);
    }

    public function currentPolicy()
    {
        return $this->hasOne(EmployeePolicyAssignment::class)
            ->whereNull('effective_to')
            ->latestOfMany('effective_from');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveApprovals()
    {
        return $this->hasMany(LeaveApproval::class, 'approver_id');
    }

    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    public function ledTeams()
    {
        return $this->hasMany(Team::class, 'leader_id');
    }

    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function projectMemberAssignments()
    {
        return $this->hasMany(ProjectMemberAssignment::class);
    }

    public function hourLogs()
    {
        return $this->hasMany(HourLog::class);
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    // =================== Role Helpers ===================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isGeneralManager(): bool
    {
        return $this->hasRole('general_manager');
    }

    public function isHR(): bool
    {
        return $this->hasRole('hr');
    }

    public function isProjectManager(): bool
    {
        return $this->hasRole('project_manager');
    }

    public function isTeamLeader(): bool
    {
        return $this->hasRole('team_leader');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }
}