<?php

namespace App\Services;

use App\Repositories\BaseRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    protected BaseRepository $repository;
    protected CacheService $cache;

    public function __construct(
        BaseRepository $repository,
        CacheService $cache
    ) {
        $this->repository = $repository;
        $this->cache      = $cache;
    }

    /**
     * Get all records
     */
    public function getAll(array $columns = ['*']): Collection
    {
        return $this->repository->all($columns);
    }

    /**
     * Get paginated records
     */
    public function getPaginated(
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): LengthAwarePaginator {
        return $this->repository->paginate($perPage, $columns, $relations);
    }

    /**
     * Find by ID
     */
    public function findById(
        int $id,
        array $columns = ['*'],
        array $relations = []
    ): ?Model {
        return $this->repository->findById($id, $columns, $relations);
    }

    /**
     * Find by ID or fail
     */
    public function findOrFail(
        int $id,
        array $columns = ['*'],
        array $relations = []
    ): Model {
        return $this->repository->findOrFail($id, $columns, $relations);
    }

    /**
     * Create a new record wrapped in transaction
     */
    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $record = $this->repository->create($data);
            $this->afterCreate($record);
            return $record;
        });
    }

    /**
     * Update a record wrapped in transaction
     */
    public function update(int $id, array $data): Model
    {
        return DB::transaction(function () use ($id, $data) {
            $record = $this->repository->findOrFail($id);
            $this->repository->update($id, $data);
            $record->refresh();
            $this->afterUpdate($record);
            return $record;
        });
    }

    /**
     * Delete a record wrapped in transaction
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $record = $this->repository->findOrFail($id);
            $result = $this->repository->delete($id);
            $this->afterDelete($record);
            return $result;
        });
    }

    /**
     * Execute within a transaction
     * Use this for complex multi-step operations
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Log info message
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info("[" . static::class . "] {$message}", $context);
    }

    /**
     * Log error message
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[" . static::class . "] {$message}", $context);
    }

    /**
     * Log warning message
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning("[" . static::class . "] {$message}", $context);
    }

    /**
     * Hook called after create
     * Override in child services to add post-create logic
     * e.g. sending notifications, invalidating cache
     */
    protected function afterCreate(Model $model): void
    {
        //
    }

    /**
     * Hook called after update
     * Override in child services to add post-update logic
     * e.g. invalidating cache, audit logging
     */
    protected function afterUpdate(Model $model): void
    {
        //
    }

    /**
     * Hook called after delete
     * Override in child services to add post-delete logic
     * e.g. cleanup, notifications
     */
    protected function afterDelete(Model $model): void
    {
        //
    }
}