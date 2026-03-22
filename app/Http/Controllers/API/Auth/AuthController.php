<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AuthService $authService
    ) {}

    #[OA\Post(
        path: '/api/v1/auth/login',
        summary: 'Login with email and password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'superadmin@company.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'password'
                    ),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'access_token', type: 'string'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'bearer'),
                                new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'employee_code', type: 'string', example: 'EMP-0001'),
                                        new OA\Property(property: 'full_name', type: 'string', example: 'Super Admin'),
                                        new OA\Property(property: 'email', type: 'string', example: 'superadmin@company.com'),
                                        new OA\Property(property: 'role', type: 'string', example: 'super_admin'),
                                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid email or password'),
                        new OA\Property(property: 'errors', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation failed'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            if (!$result['success']) {
                return $this->errorResponse($result['message'], 401);
            }

            return $this->successResponse(
                array_merge(
                    $this->authService->buildTokenResponse($result['token']),
                    ['user' => new UserResource($result['user'])]
                ),
                'Login successful'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/auth/logout',
        summary: 'Logout and invalidate JWT token',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully'),
                        new OA\Property(property: 'data', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized — token missing or invalid'),
        ]
    )]
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            return $this->successResponse(null, 'Logged out successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/auth/refresh',
        summary: 'Refresh JWT token and get a new one',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token refreshed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Token refreshed successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'access_token', type: 'string'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'bearer'),
                                new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized — token expired or invalid'),
        ]
    )]
    public function refresh(): JsonResponse
    {
        try {
            $token = $this->authService->refresh();
            if (!$token) {
                return $this->unauthorizedResponse('Unable to refresh token');
            }
            return $this->successResponse(
                $this->authService->buildTokenResponse($token),
                'Token refreshed successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/auth/me',
        summary: 'Get currently authenticated user details',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated user retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'employee_code', type: 'string', example: 'EMP-0001'),
                                new OA\Property(property: 'full_name', type: 'string', example: 'Super Admin'),
                                new OA\Property(property: 'email', type: 'string', example: 'superadmin@company.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'super_admin'),
                                new OA\Property(property: 'department', type: 'string', example: 'Management'),
                                new OA\Property(property: 'designation', type: 'string', example: 'Super Administrator'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function me(): JsonResponse
    {
        try {
            $user = $this->authService->me();
            if (!$user) {
                return $this->unauthorizedResponse();
            }
            return $this->successResponse(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}