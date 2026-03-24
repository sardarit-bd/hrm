<?php

namespace App\Repositories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Collection;

class ShiftRepository extends BaseRepository
{
    public function __construct(Shift $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all shifts ordered by name
     */
    public function getAllOrdered(): Collection
    {
        return $this->model
            ->orderBy('name')
            ->get();
    }

    /**
     * Get fixed shifts only
     */
    public function getFixed(): Collection
    {
        return $this->model
            ->where('is_fixed', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get rotating shifts only
     */
    public function getRotating(): Collection
    {
        return $this->model
            ->where('is_fixed', false)
            ->orderBy('name')
            ->get();
    }
}