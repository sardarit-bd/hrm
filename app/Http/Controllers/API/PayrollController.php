<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\GeneratePayrollRequest;
use App\Http\Requests\Payroll\UpdatePayrollRequest;
use App\Http\Resources\PayrollRecordResource;
use App\Services\PayrollService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PayrollController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected PayrollService $payrollService
    ) {}

    #[OA\Get(
        path: '/api/v1/payroll',
        summary: 'List all payroll records with filters and pagination',
        security: [['bearerAuth' => []]],
        tags: ['Payroll'],
        parameters: [
            new OA\Parameter(
                name: 'user_id',
                in: 'query',
                required: false,
                description: 'Filter by employee ID',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'payroll_status',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['draft', 'approved', 'paid']
                )
            ),
            new OA\Parameter(
                name: 'payroll_month',
                in: 'query',
                required: false,
                description: 'Filter by specific month — format: Y-m e.g. 2026-03',
                schema: new OA\Schema(type: 'string', example: '2026-03')
            ),
            new OA\Parameter(
                name: 'year',
                in: 'query',
                required: false,
                description: 'Filter by year',
                schema: new OA\Schema(type: 'integer', example: 2026)
            ),
            new OA\Parameter(
                name: 'quarter',
                in: 'query',
                required: false,
                description: 'Filter by quarter (1-4) — must be used with year',
                schema: new OA\Schema(type: 'integer', enum: [1, 2, 3, 4])
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payroll records retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Payroll records retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'user', type: 'object'),
                                    new OA\Property(property: 'payroll_month', type: 'string', example: '2026-03'),
                                    new OA\Property(property: 'basic_salary', type: 'number', example: 50000),
                                    new OA\Property(property: 'gross_salary', type: 'number', example: 50000),
                                    new OA\Property(property: 'net_salary', type: 'number', example: 47500),
                                    new OA\Property(property: 'total_working_days', type: 'integer', example: 26),
                                    new OA\Property(property: 'days_present', type: 'integer', example: 24),
                                    new OA\Property(property: 'days_absent', type: 'integer', example: 2),
                                    new OA\Property(property: 'late_count', type: 'integer', example: 3),
                                    new OA\Property(property: 'late_deduction_amount', type: 'number', example: 0),
                                    new OA\Property(property: 'absent_deduction_amount', type: 'number', example: 2500),
                                    new OA\Property(
                                        property: 'payroll_status',
                                        type: 'string',
                                        enum: ['draft', 'approved', 'paid']
                                    ),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 50),
                                new OA\Property(property: 'last_page', type: 'integer', example: 4),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters  = $request->only(['user_id', 'payroll_status', 'payroll_month', 'year', 'quarter']);
            $perPage  = $request->integer('per_page', 15);
            $payrolls = $this->payrollService->getPaginatedPayrolls($filters, $perPage);

            return $this->paginatedResponse(
                PayrollRecordResource::collection($payrolls),
                'Payroll records retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/payroll/generate',
        summary: 'Generate payroll for a single employee',
        description: 'Calculates payroll based on attendance records, policy deductions and carry forward from previous month. Creates a draft record.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id', 'payroll_month'],
                properties: [
                    new OA\Property(
                        property: 'user_id',
                        type: 'integer',
                        example: 5,
                        description: 'ID of the employee'
                    ),
                    new OA\Property(
                        property: 'payroll_month',
                        type: 'string',
                        example: '2026-03',
                        description: 'Month to generate payroll for — format: Y-m'
                    ),
                ]
            )
        ),
        tags: ['Payroll'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Payroll generated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Payroll generated successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'payroll_month', type: 'string', example: '2026-03'),
                                new OA\Property(property: 'basic_salary', type: 'number', example: 50000),
                                new OA\Property(property: 'net_salary', type: 'number', example: 47500),
                                new OA\Property(property: 'payroll_status', type: 'string', example: 'draft'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 422, description: 'Payroll already exists or missing salary/policy'),
        ]
    )]
    public function generate(GeneratePayrollRequest $request): JsonResponse
    {
        try {
            $payroll = $this->payrollService->generatePayroll(
                $request->user_id,
                $request->payroll_month
            );
            return $this->createdResponse(
                new PayrollRecordResource($payroll),
                'Payroll generated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/payroll/generate/bulk',
        summary: 'Generate payroll for all active employees',
        description: 'Generates payroll for all active employees for the specified month. Returns success and failed counts.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['payroll_month'],
                properties: [
                    new OA\Property(
                        property: 'payroll_month',
                        type: 'string',
                        example: '2026-03',
                        description: 'Month to generate payroll for — format: Y-m'
                    ),
                ]
            )
        ),
        tags: ['Payroll'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Bulk payroll generation completed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Bulk payroll generation completed'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'success',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'user_id', type: 'integer', example: 5),
                                            new OA\Property(property: 'full_name', type: 'string', example: 'John Doe'),
                                            new OA\Property(property: 'net_salary', type: 'number', example: 47500),
                                        ]
                                    )
                                ),
                                new OA\Property(
                                    property: 'failed',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'user_id', type: 'integer', example: 6),
                                            new OA\Property(property: 'full_name', type: 'string', example: 'Jane Doe'),
                                            new OA\Property(property: 'reason', type: 'string', example: 'No salary found'),
                                        ]
                                    )
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function generateBulk(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'payroll_month' => ['required', 'date_format:Y-m'],
            ]);
            $results = $this->payrollService->generateBulkPayroll($request->payroll_month);
            return $this->successResponse($results, 'Bulk payroll generation completed');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/payroll/{id}',
        summary: 'Get payroll record by ID with itemized deduction logs',
        security: [['bearerAuth' => []]],
        tags: ['Payroll'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payroll record retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Payroll record retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'user', type: 'object'),
                                new OA\Property(property: 'attendance_policy', type: 'object'),
                                new OA\Property(property: 'payroll_month', type: 'string', example: '2026-03'),
                                new OA\Property(property: 'basic_salary', type: 'number', example: 50000),
                                new OA\Property(property: 'gross_salary', type: 'number', example: 50000),
                                new OA\Property(property: 'net_salary', type: 'number', example: 47500),
                                new OA\Property(property: 'total_deductions', type: 'number', example: 2500),
                                new OA\Property(property: 'total_working_days', type: 'integer', example: 26),
                                new OA\Property(property: 'days_present', type: 'integer', example: 24),
                                new OA\Property(property: 'days_absent', type: 'integer', example: 2),
                                new OA\Property(property: 'late_count', type: 'integer', example: 3),
                                new OA\Property(property: 'late_carry_forward_in', type: 'integer', example: 0),
                                new OA\Property(property: 'late_carry_forward_out', type: 'integer', example: 3),
                                new OA\Property(property: 'payroll_status', type: 'string', example: 'draft'),
                                new OA\Property(
                                    property: 'deduction_logs',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'deduction_type', type: 'string', example: 'absent'),
                                            new OA\Property(property: 'reference_date', type: 'string', format: 'date'),
                                            new OA\Property(property: 'deduction_amount', type: 'number', example: 1250),
                                            new OA\Property(property: 'note', type: 'string', nullable: true),
                                        ]
                                    )
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Payroll record not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $payroll = $this->payrollService->findOrFail(
                $id,
                ['*'],
                ['user', 'attendancePolicy', 'approvedBy', 'lateDeductionLogs']
            );
            return $this->successResponse(
                new PayrollRecordResource($payroll),
                'Payroll record retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Payroll record not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/payroll/{id}',
        summary: 'Update payroll remarks or status',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'remarks',
                        type: 'string',
                        nullable: true,
                        example: 'Reviewed and confirmed'
                    ),
                    new OA\Property(
                        property: 'payroll_status',
                        type: 'string',
                        enum: ['draft', 'approved', 'paid']
                    ),
                ]
            )
        ),
        tags: ['Payroll'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Payroll record updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 404, description: 'Payroll record not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdatePayrollRequest $request, int $id): JsonResponse
    {
        try {
            $payroll = $this->payrollService->update($id, $request->validated());
            return $this->successResponse(
                new PayrollRecordResource($payroll),
                'Payroll record updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Payroll record not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Patch(
        path: '/api/v1/payroll/{id}/approve',
        summary: 'Approve a draft payroll record',
        description: 'Only draft payrolls can be approved. Records who approved and when.',
        security: [['bearerAuth' => []]],
        tags: ['Payroll'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payroll approved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Payroll approved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'payroll_status', type: 'string', example: 'approved'),
                                new OA\Property(property: 'approved_by', type: 'object'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 422, description: 'Only draft payrolls can be approved'),
        ]
    )]
    public function approve(int $id, Request $request): JsonResponse
    {
        try {
            $payroll = $this->payrollService->approvePayroll($id, $request->auth_user->id);
            return $this->successResponse(
                new PayrollRecordResource($payroll),
                'Payroll approved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Patch(
        path: '/api/v1/payroll/{id}/paid',
        summary: 'Mark approved payroll as paid',
        description: 'Only approved payrolls can be marked as paid. Records the paid_at timestamp.',
        security: [['bearerAuth' => []]],
        tags: ['Payroll'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payroll marked as paid successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Payroll marked as paid successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'payroll_status', type: 'string', example: 'paid'),
                                new OA\Property(property: 'paid_at', type: 'string', format: 'datetime'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 422, description: 'Only approved payrolls can be marked as paid'),
        ]
    )]
    public function markAsPaid(int $id): JsonResponse
    {
        try {
            $payroll = $this->payrollService->markAsPaid($id);
            return $this->successResponse(
                new PayrollRecordResource($payroll),
                'Payroll marked as paid successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/payroll/user/{userId}/month/{month}',
        summary: 'Get payroll record for a specific employee and month',
        security: [['bearerAuth' => []]],
        tags: ['Payroll'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                description: 'ID of the employee',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
            new OA\Parameter(
                name: 'month',
                in: 'path',
                required: true,
                description: 'Month in Y-m format',
                schema: new OA\Schema(type: 'string', example: '2026-03')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Payroll record retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 404, description: 'Payroll not found for this user and month'),
        ]
    )]
    public function getByUserAndMonth(int $userId, string $month): JsonResponse
    {
        try {
            $payroll = $this->payrollService->getByUserAndMonth($userId, $month);
            if (!$payroll) {
                return $this->notFoundResponse('Payroll not found for this user and month');
            }
            return $this->successResponse(
                new PayrollRecordResource($payroll),
                'Payroll record retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/payroll/user/{userId}/quarterly',
        summary: 'Get quarterly payroll summary for an employee',
        description: 'Returns 3 monthly payroll records with totals for the specified quarter',
        security: [['bearerAuth' => []]],
        tags: ['Payroll'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                description: 'ID of the employee',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
            new OA\Parameter(
                name: 'year',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 2026)
            ),
            new OA\Parameter(
                name: 'quarter',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'integer', enum: [1, 2, 3, 4], example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Quarterly summary retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Quarterly summary retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'records', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'total_gross_salary', type: 'number', example: 150000),
                                new OA\Property(property: 'total_net_salary', type: 'number', example: 142500),
                                new OA\Property(property: 'total_deductions', type: 'number', example: 7500),
                                new OA\Property(property: 'total_days_present', type: 'integer', example: 72),
                                new OA\Property(property: 'total_days_absent', type: 'integer', example: 6),
                                new OA\Property(property: 'total_late_count', type: 'integer', example: 9),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function quarterlySummary(int $userId, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'year'    => ['required', 'integer', 'min:2000'],
                'quarter' => ['required', 'integer', 'min:1', 'max:4'],
            ]);
            $summary = $this->payrollService->getQuarterlySummary(
                $userId,
                $request->year,
                $request->quarter
            );
            return $this->successResponse($summary, 'Quarterly summary retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/payroll/my',
        summary: 'Get authenticated employee own payroll records',
        security: [['bearerAuth' => []]],
        tags: ['Payroll'],
        parameters: [
            new OA\Parameter(
                name: 'payroll_status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['draft', 'approved', 'paid'])
            ),
            new OA\Parameter(
                name: 'payroll_month',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: '2026-03')
            ),
            new OA\Parameter(
                name: 'year',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 2026)
            ),
            new OA\Parameter(
                name: 'quarter',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', enum: [1, 2, 3, 4])
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Payroll records retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function myPayroll(Request $request): JsonResponse
    {
        try {
            $filters            = $request->only(['payroll_status', 'payroll_month', 'year', 'quarter']);
            $filters['user_id'] = $request->auth_user->id;
            $perPage            = $request->integer('per_page', 15);
            $payrolls           = $this->payrollService->getPaginatedPayrolls($filters, $perPage);
            return $this->paginatedResponse($payrolls, 'Payroll records retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}