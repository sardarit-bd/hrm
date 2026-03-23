<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class PermissionMiddleware
{
    use ApiResponseTrait;

    /**
     * Usage in routes:
     * ->middleware('permission:users.view')
     * ->middleware('permission:users.view,users.create')  — requires ALL
     * ->middleware('role:super_admin')                    — role check
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$permissions
    ): mixed {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not found');
            }

            if (!$user->isActive()) {
                return $this->forbiddenResponse(
                    'Your account has been deactivated'
                );
            }

            // Check permissions if provided
            if (!empty($permissions)) {
                foreach ($permissions as $permission) {
                    if (!$user->hasPermissionTo($permission)) {
                        return $this->forbiddenResponse(
                            "You do not have permission to perform this action: {$permission}"
                        );
                    }
                }
            }

            // Bind authenticated user to request
            $request->merge(['auth_user' => $user]);

        } catch (TokenExpiredException $e) {
            return $this->unauthorizedResponse('Token has expired');
        } catch (TokenInvalidException $e) {
            return $this->unauthorizedResponse('Token is invalid');
        } catch (JWTException $e) {
            return $this->unauthorizedResponse('Token not provided');
        }

        return $next($request);
    }
}