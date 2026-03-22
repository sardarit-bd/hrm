<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class ZktecoSyncMiddleware
{
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next): mixed
    {
        $apiKey      = $request->header('X-Sync-Key');
        $validApiKey = config('zkteco.sync_api_key');

        if (!$apiKey || $apiKey !== $validApiKey) {
            return $this->unauthorizedResponse(
                'Invalid or missing sync key'
            );
        }

        return $next($request);
    }
}