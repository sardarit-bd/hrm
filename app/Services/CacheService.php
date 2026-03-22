<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    protected static ?string $resolvedDriver = null;
    protected int $defaultTtl;

    public function __construct()
    {
        $this->defaultTtl = config('cache.ttl', 3600);

        if (static::$resolvedDriver === null) {
            static::$resolvedDriver = $this->resolveDriver();
        }
    }

    /**
     * Resolve driver once at boot.
     * Static property persists for entire process lifetime.
     */
    private function resolveDriver(): string
    {
        if (config('cache.default') === 'redis') {
            try {
                Redis::ping();
                return 'redis';
            } catch (\Exception $e) {
                report($e);
                return config('cache.fallback', 'file');
            }
        }

        return config('cache.default', 'file');
    }

    /**
     * Get cache store using resolved driver
     */
    private function store(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store(static::$resolvedDriver);
    }

    /**
     * Get current cache driver
     */
    public function getDriver(): string
    {
        return static::$resolvedDriver;
    }

    /**
     * Check if using Redis
     */
    public function isRedis(): bool
    {
        return static::$resolvedDriver === 'redis';
    }

    /**
     * Get or store value in cache
     */
    public function remember(
        string $key,
        callable $callback,
        ?int $ttl = null
    ): mixed {
        return $this->store()->remember(
            $key,
            $ttl ?? $this->defaultTtl,
            $callback
        );
    }

    /**
     * Get value from cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store()->get($key, $default);
    }

    /**
     * Store value in cache
     */
    public function put(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        return $this->store()->put(
            $key,
            $value,
            $ttl ?? $this->defaultTtl
        );
    }

    /**
     * Check if key exists
     */
    public function has(string $key): bool
    {
        return $this->store()->has($key);
    }

    /**
     * Remove value from cache
     */
    public function forget(string $key): bool
    {
        return $this->store()->forget($key);
    }

    /**
     * Remove multiple keys
     */
    public function forgetMultiple(array $keys): void
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Flush all cache
     */
    public function flush(): bool
    {
        return $this->store()->flush();
    }

    /**
     * Store value forever
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->store()->forever($key, $value);
    }

    // =================== App Specific Cache Keys ===================

    public function rememberActiveRoster(int $userId, callable $callback): mixed
    {
        return $this->remember(
            "roster.active.user.{$userId}",
            $callback,
            86400
        );
    }

    public function forgetActiveRoster(int $userId): bool
    {
        return $this->forget("roster.active.user.{$userId}");
    }

    public function rememberActivePolicy(int $userId, callable $callback): mixed
    {
        return $this->remember(
            "policy.active.user.{$userId}",
            $callback,
            86400
        );
    }

    public function forgetActivePolicy(int $userId): bool
    {
        return $this->forget("policy.active.user.{$userId}");
    }

    public function rememberActiveSalary(int $userId, callable $callback): mixed
    {
        return $this->remember(
            "salary.active.user.{$userId}",
            $callback,
            86400
        );
    }

    public function forgetActiveSalary(int $userId): bool
    {
        return $this->forget("salary.active.user.{$userId}");
    }

    public function rememberHolidays(int $year, callable $callback): mixed
    {
        return $this->remember(
            "holidays.year.{$year}",
            $callback,
            604800
        );
    }

    public function forgetHolidays(int $year): bool
    {
        return $this->forget("holidays.year.{$year}");
    }

    public function rememberPayrollSummary(
        int $userId,
        string $month,
        callable $callback
    ): mixed {
        return $this->remember(
            "payroll.summary.user.{$userId}.month.{$month}",
            $callback,
            3600
        );
    }

    public function forgetPayrollSummary(int $userId, string $month): bool
    {
        return $this->forget(
            "payroll.summary.user.{$userId}.month.{$month}"
        );
    }

    public function rememberShifts(callable $callback): mixed
    {
        return $this->remember('shifts.all', $callback, 86400);
    }

    public function forgetShifts(): bool
    {
        return $this->forget('shifts.all');
    }

    public function rememberLeaveTypes(callable $callback): mixed
    {
        return $this->remember('leave_types.all', $callback, 86400);
    }

    public function forgetLeaveTypes(): bool
    {
        return $this->forget('leave_types.all');
    }

    public function forgetUserCache(int $userId): void
    {
        $this->forgetMultiple([
            "roster.active.user.{$userId}",
            "policy.active.user.{$userId}",
            "salary.active.user.{$userId}",
        ]);
    }
}