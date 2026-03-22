<?php

namespace App\Repositories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Collection;

class LeaveTypeRepository extends BaseRepository
{
    public function __construct(LeaveType $model)
    {
        parent::__construct($model);
    }

    public function getPaidLeaveTypes(): Collection
    {
        return $this->model
            ->where('is_paid', true)
            ->orderBy('name')
            ->get();
    }

    public function getUnpaidLeaveTypes(): Collection
    {
        return $this->model
            ->where('is_paid', false)
            ->orderBy('name')
            ->get();
    }

    public function getAllOrdered(): Collection
    {
        return $this->model
            ->orderBy('name')
            ->get();
    }
}