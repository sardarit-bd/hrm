<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use App\Services\CacheService;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService extends BaseService
{
    public function __construct(
        AuthRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Attempt login and return token
     */
    public function login(array $credentials): array
    {
        $user = $this->repository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return [
                'success' => false,
                'message' => 'Invalid email or password',
            ];
        }

        if (!$user->isActive()) {
            return [
                'success' => false,
                'message' => 'Your account has been deactivated',
            ];
        }

        $token = JWTAuth::fromUser($user);

        $this->logInfo('User logged in', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'role'    => $user->role,
        ]);

        return [
            'success' => true,
            'token'   => $token,
            'user'    => $user,
        ];
    }

    /**
     * Logout and invalidate token
     */
    public function logout(): bool
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return true;
        } catch (JWTException $e) {
            $this->logError('Logout failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Refresh token
     */
    public function refresh(): ?string
    {
        try {
            return JWTAuth::refresh(JWTAuth::getToken());
        } catch (JWTException $e) {
            $this->logError('Token refresh failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get authenticated user
     */
    public function me(): ?User
    {
        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return null;
        }
    }

    /**
     * Build token response array
     */
    public function buildTokenResponse(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => config('jwt.ttl') * 60,
        ];
    }
}