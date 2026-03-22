<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->get();
    }

    /**
     * Get paginated records
     */
    public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): LengthAwarePaginator {
        return $this->model
            ->select($columns)
            ->with($relations)
            ->paginate($perPage);
    }

    /**
     * Find by ID
     */
    public function findById(
        int $id,
        array $columns = ['*'],
        array $relations = []
    ): ?Model {
        return $this->model
            ->select($columns)
            ->with($relations)
            ->find($id);
    }

    /**
     * Find by ID or fail
     */
    public function findOrFail(
        int $id,
        array $columns = ['*'],
        array $relations = []
    ): Model {
        return $this->model
            ->select($columns)
            ->with($relations)
            ->findOrFail($id);
    }

    /**
     * Find by column value
     */
    public function findBy(
        string $column,
        mixed $value,
        array $columns = ['*'],
        array $relations = []
    ): ?Model {
        return $this->model
            ->select($columns)
            ->with($relations)
            ->where($column, $value)
            ->first();
    }

    /**
     * Get all records by column value
     */
    public function getAllBy(
        string $column,
        mixed $value,
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        return $this->model
            ->select($columns)
            ->with($relations)
            ->where($column, $value)
            ->get();
    }

    /**
     * Create a new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool
    {
        return $this->model
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Update or create a record
     */
    public function updateOrCreate(
        array $conditions,
        array $data
    ): Model {
        return $this->model->updateOrCreate($conditions, $data);
    }

    /**
     * Delete a record
     */
    public function delete(int $id): bool
    {
        return $this->model
            ->where('id', $id)
            ->delete();
    }

    /**
     * Check if record exists
     */
    public function exists(string $column, mixed $value): bool
    {
        return $this->model
            ->where($column, $value)
            ->exists();
    }

    /**
     * Count records
     */
    public function count(array $conditions = []): int
    {
        $query = $this->model->newQuery();

        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        return $query->count();
    }

    /**
     * Get records with filters
     */
    public function filter(
        array $conditions = [],
        array $columns = ['*'],
        array $relations = [],
        ?string $orderBy = null,
        string $direction = 'asc'
    ): Collection {
        $query = $this->model
            ->select($columns)
            ->with($relations);

        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        if ($orderBy) {
            $query->orderBy($orderBy, $direction);
        }

        return $query->get();
    }

    /**
     * Get paginated records with filters
     */
    public function filterPaginate(
        array $conditions = [],
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = [],
        ?string $orderBy = null,
        string $direction = 'asc'
    ): LengthAwarePaginator {
        $query = $this->model
            ->select($columns)
            ->with($relations);

        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        if ($orderBy) {
            $query->orderBy($orderBy, $direction);
        }

        return $query->paginate($perPage);
    }

    /**
     * Bulk insert records
     */
    public function bulkInsert(array $data): bool
    {
        return $this->model->insert($data);
    }

    /**
     * Bulk update records
     */
    public function bulkUpdate(
        array $conditions,
        array $data
    ): int {
        $query = $this->model->newQuery();

        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        return $query->update($data);
    }

    /**
     * Get first or create
     */
    public function firstOrCreate(
        array $conditions,
        array $data = []
    ): Model {
        return $this->model->firstOrCreate($conditions, $data);
    }

    /**
     * Get latest records
     */
    public function latest(
        int $limit = 10,
        string $column = 'created_at',
        array $relations = []
    ): Collection {
        return $this->model
            ->with($relations)
            ->orderByDesc($column)
            ->limit($limit)
            ->get();
    }

    /**
     * Get model instance
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Begin a new query
     */
    public function newQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery();
    }
}