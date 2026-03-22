<?php

namespace App\Http\Controllers\API\Zkteco;

use App\Http\Controllers\Controller;
use App\Http\Requests\Zkteco\ZktecoSyncRequest;
use App\Jobs\ProcessZktecoPunchesJob;
use App\Models\ZkPunchLog;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class ZktecoSyncController extends Controller
{
    use ApiResponseTrait;

    #[OA\Post(
        path: '/api/v1/zkteco/sync',
        summary: 'Receive punch data pushed from ZKTeco local agent',
        description: 'This endpoint is called by the local agent running on the machine where ZKTeco device is connected. Requires X-Sync-Key header for authentication. Creates new punch records or updates existing ones.',
        tags: ['ZKTeco'],
        parameters: [
            new OA\Parameter(
                name: 'X-Sync-Key',
                in: 'header',
                required: true,
                description: 'Secret API key for local agent authentication',
                schema: new OA\Schema(type: 'string', example: 'your-sync-api-key-here')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['device_id', 'synced_at', 'punches'],
                properties: [
                    new OA\Property(
                        property: 'device_id',
                        type: 'string',
                        example: 'DEVICE_001',
                        description: 'Unique identifier of the ZKTeco device'
                    ),
                    new OA\Property(
                        property: 'synced_at',
                        type: 'string',
                        format: 'datetime',
                        example: '2026-03-22 09:00:00',
                        description: 'Timestamp of when the sync was initiated'
                    ),
                    new OA\Property(
                        property: 'punches',
                        type: 'array',
                        description: 'Array of punch records from the device',
                        items: new OA\Items(
                            required: ['uid', 'id', 'punch_time', 'type'],
                            properties: [
                                new OA\Property(
                                    property: 'uid',
                                    type: 'integer',
                                    example: 1,
                                    description: 'Device internal UID — auto-incremented by device'
                                ),
                                new OA\Property(
                                    property: 'id',
                                    type: 'string',
                                    example: 'EMP-0001',
                                    description: 'Employee code enrolled on the device'
                                ),
                                new OA\Property(
                                    property: 'punch_time',
                                    type: 'string',
                                    format: 'datetime',
                                    example: '2026-03-22 09:05:00',
                                    description: 'Timestamp of the punch'
                                ),
                                new OA\Property(
                                    property: 'type',
                                    type: 'integer',
                                    enum: [0, 1],
                                    example: 1,
                                    description: '1 = entry (check-in), 0 = exit (check-out)'
                                ),
                                new OA\Property(
                                    property: 'state',
                                    type: 'integer',
                                    example: 0,
                                    nullable: true,
                                    description: 'Device state code — raw value from device'
                                ),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Punches synced successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Punches synced successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'received',
                                    type: 'integer',
                                    example: 10,
                                    description: 'Total punch records received'
                                ),
                                new OA\Property(
                                    property: 'created',
                                    type: 'integer',
                                    example: 8,
                                    description: 'New punch records created'
                                ),
                                new OA\Property(
                                    property: 'updated',
                                    type: 'integer',
                                    example: 2,
                                    description: 'Existing punch records updated'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized — Invalid or missing X-Sync-Key'),
            new OA\Response(response: 403, description: 'Forbidden — Device not in whitelist'),
            new OA\Response(response: 422, description: 'Validation error in punch data'),
        ]
    )]
    public function sync(ZktecoSyncRequest $request): JsonResponse
    {
        try {
            $deviceId = $request->device_id;
            $punches  = $request->punches;
            $created  = 0;
            $updated  = 0;

            if (!$this->isAllowedDevice($deviceId)) {
                return $this->forbiddenResponse("Device {$deviceId} is not whitelisted");
            }

            foreach ($punches as $punch) {
                $existing = ZkPunchLog::where('zk_uid', $punch['uid'])
                    ->where('device_id', $deviceId)
                    ->first();

                ZkPunchLog::updateOrCreate(
                    [
                        'zk_uid'    => $punch['uid'],
                        'device_id' => $deviceId,
                    ],
                    [
                        'employee_code' => $punch['id'],
                        'state'         => $punch['state'] ?? null,
                        'punch_time'    => $punch['punch_time'],
                        'punch_type'    => $punch['type'] == 1 ? 'entry' : 'exit',
                        'synced_at'     => $request->synced_at,
                        'is_processed'  => false,
                    ]
                );

                $existing ? $updated++ : $created++;
            }

            if ($created > 0 || $updated > 0) {
                ProcessZktecoPunchesJob::dispatch();
            }

            Log::info('[ZKTeco Sync] Push received', [
                'device_id' => $deviceId,
                'received'  => count($punches),
                'created'   => $created,
                'updated'   => $updated,
            ]);

            return $this->successResponse(
                [
                    'received' => count($punches),
                    'created'  => $created,
                    'updated'  => $updated,
                ],
                'Punches synced successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    private function isAllowedDevice(string $deviceId): bool
    {
        $allowedDevices = config('zkteco.allowed_devices', []);
        if (empty($allowedDevices)) {
            return true;
        }
        return in_array($deviceId, $allowedDevices);
    }
}