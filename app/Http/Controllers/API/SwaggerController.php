<?php

namespace App\Http\Controllers\API;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Human Resource Management System API — Sardar IT',
    title: 'HRM API',
    contact: new OA\Contact(
        name: 'HRM Support',
        email: 'admin@sardarit.com'
    )
)]

#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter your JWT token'
)]
#[OA\Tag(name: 'Auth', description: 'Authentication — login, logout, refresh, me')]
#[OA\Tag(name: 'Users', description: 'User management — CRUD, role, status')]
#[OA\Tag(name: 'Shifts', description: 'Shift management — fixed and rotating')]
#[OA\Tag(name: 'Roster', description: 'Roster assignments — weekends and shift history')]
#[OA\Tag(name: 'Attendance Policies', description: 'Policy management and employee assignment')]
#[OA\Tag(name: 'Leave Types', description: 'Leave type configuration')]
#[OA\Tag(name: 'Leave Requests', description: 'Leave application and approval workflow')]
#[OA\Tag(name: 'Projects', description: 'Project management — single, milestone, hourly')]
#[OA\Tag(name: 'Teams', description: 'Team management and project assignments')]
#[OA\Tag(name: 'Milestones', description: 'Project milestones and completion tracking')]
#[OA\Tag(name: 'Hour Logs', description: 'Hourly project time tracking')]
#[OA\Tag(name: 'Payroll', description: 'Payroll generation, approval and payment')]
#[OA\Tag(name: 'Feedback', description: 'Anonymous feedback submission and review')]
#[OA\Tag(name: 'Holidays', description: 'Public and company holiday management')]
#[OA\Tag(name: 'Notifications', description: 'User notification management')]
#[OA\Tag(name: 'ZKTeco', description: 'ZKTeco device punch sync endpoint')]
class SwaggerController {}