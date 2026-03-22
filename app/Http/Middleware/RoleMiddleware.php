<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\ApiResponseTrait;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class RoleMiddleware
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     * Usage in routes:
     * ->middleware('role:super_admin')
     * ->middleware('role:super_admin,general_manager')
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
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

            if (!empty($roles) && !in_array($user->role, $roles)) {
                return $this->forbiddenResponse(
                    'You do not have permission to access this resource'
                );
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