<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    /**
     * Get all roles with permissions
     */
    public function getAllWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * Find role by name
     */
    public function findByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }

    /**
     * Find role by ID
     */
    public function findById(int $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }

    /**
     * Get permissions grouped by module
     */
    public function getPermissionsGrouped(): array
    {
        $permissions = Permission::orderBy('name')->get();

        return $permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        })->toArray();
    }

    /**
     * Sync permissions for role
     */
    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);
        return $role->fresh('permissions');
    }

    /**
     * Give permission to role
     */
    public function givePermission(Role $role, string $permission): Role
    {
        $role->givePermissionTo($permission);
        return $role->fresh('permissions');
    }

    /**
     * Revoke permission from role
     */
    public function revokePermission(Role $role, string $permission): Role
    {
        $role->revokePermissionTo($permission);
        return $role->fresh('permissions');
    }
}