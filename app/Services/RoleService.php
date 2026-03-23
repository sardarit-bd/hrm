<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\RoleRepository;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleService
{
    public function __construct(
        protected RoleRepository $repository,
        protected CacheService $cache
    ) {}

    /**
     * Get all roles with permissions
     */
    public function getAllRoles(): Collection
    {
        return $this->cache->remember(
            'roles.all',
            fn() => $this->repository->getAllWithPermissions(),
            86400
        );
    }

    /**
     * Get all permissions grouped by module
     */
    public function getAllPermissions(): array
    {
        return $this->cache->remember(
            'permissions.grouped',
            fn() => $this->repository->getPermissionsGrouped(),
            86400
        );
    }

    /**
     * Get role by ID with permissions
     */
    public function getRoleById(int $id): ?Role
    {
        return $this->repository->findById($id);
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissionsToRole(
        int $roleId,
        array $permissions
    ): Role {
        $role = $this->repository->findById($roleId);

        if (!$role) {
            throw new \Exception('Role not found');
        }

        DB::transaction(function () use ($role, $permissions) {
            $this->repository->syncPermissions($role, $permissions);
            $this->invalidatePermissionCache();
        });

        Log::info('[RoleService] Permissions assigned to role', [
            'role'        => $role->name,
            'permissions' => $permissions,
        ]);

        return $role->fresh('permissions');
    }

    /**
     * Give single permission to role
     */
    public function givePermissionToRole(
        int $roleId,
        string $permission
    ): Role {
        $role = $this->repository->findById($roleId);

        if (!$role) {
            throw new \Exception('Role not found');
        }

        $role = $this->repository->givePermission($role, $permission);
        $this->invalidatePermissionCache();

        Log::info('[RoleService] Permission given to role', [
            'role'       => $role->name,
            'permission' => $permission,
        ]);

        return $role;
    }

    /**
     * Revoke permission from role
     */
    public function revokePermissionFromRole(
        int $roleId,
        string $permission
    ): Role {
        $role = $this->repository->findById($roleId);

        if (!$role) {
            throw new \Exception('Role not found');
        }

        $role = $this->repository->revokePermission($role, $permission);
        $this->invalidatePermissionCache();

        Log::info('[RoleService] Permission revoked from role', [
            'role'       => $role->name,
            'permission' => $permission,
        ]);

        return $role;
    }

    /**
     * Assign role to user
     */
    public function assignRoleToUser(int $userId, string $roleName): User
    {
        $user = User::findOrFail($userId);
        $role = $this->repository->findByName($roleName);

        if (!$role) {
            throw new \Exception("Role {$roleName} not found");
        }

        // One role per user — sync replaces existing role
        $user->syncRoles([$roleName]);

        $this->cache->forget("user.permissions.{$userId}");

        Log::info('[RoleService] Role assigned to user', [
            'user_id' => $userId,
            'role'    => $roleName,
        ]);

        return $user->fresh('roles', 'permissions');
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions(int $userId): array
    {
        return $this->cache->remember(
            "user.permissions.{$userId}",
            function () use ($userId) {
                $user = User::findOrFail($userId);
                return [
                    'roles'       => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ];
            },
            3600
        );
    }

    /**
     * Invalidate all permission caches
     */
    private function invalidatePermissionCache(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->cache->forget('roles.all');
        $this->cache->forget('permissions.grouped');
    }
}