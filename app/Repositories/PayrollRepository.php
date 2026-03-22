<?php

namespace App\Repositories;

use App\Models\PayrollRecord;
use App\Models\LateDeductionLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PayrollRepository extends BaseRepository
{
    public function __construct(PayrollRecord $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated payroll records with filters
     */
    public function getPaginatedWithFilters(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model
            ->with(['user', 'attendancePolicy', 'approvedBy']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['payroll_status'])) {
            $query->where('payroll_status', $filters['payroll_status']);
        }

        if (!empty($filters['payroll_month'])) {
            $query->whereYear(
                'payroll_month',
                substr($filters['payroll_month'], 0, 4)
            )->whereMonth(
                'payroll_month',
                substr($filters['payroll_month'], 5, 2)
            );
        }

        if (!empty($filters['year'])) {
            $query->whereYear('payroll_month', $filters['year']);
        }

        if (!empty($filters['quarter'])) {
            $startMonth = ($filters['quarter'] - 1) * 3 + 1;
            $endMonth   = $startMonth + 2;
            $query->whereYear('payroll_month', $filters['year'] ?? now()->year)
                ->whereMonth('payroll_month', '>=', $startMonth)
                ->whereMonth('payroll_month', '<=', $endMonth);
        }

        return $query
            ->orderByDesc('payroll_month')
            ->paginate($perPage);
    }

    /**
     * Get payroll record by user and month
     */
    public function getByUserAndMonth(
        int $userId,
        string $month
    ): ?PayrollRecord {
        return $this->model
            ->with(['user', 'attendancePolicy', 'lateDeductionLogs'])
            ->where('user_id', $userId)
            ->whereYear('payroll_month', substr($month, 0, 4))
            ->whereMonth('payroll_month', substr($month, 5, 2))
            ->first();
    }

    /**
     * Check if payroll exists for user and month
     */
    public function existsForUserAndMonth(
        int $userId,
        string $month
    ): bool {
        return $this->model
            ->where('user_id', $userId)
            ->whereYear('payroll_month', substr($month, 0, 4))
            ->whereMonth('payroll_month', substr($month, 5, 2))
            ->exists();
    }

    /**
     * Get previous month payroll for carry forward
     */
    public function getPreviousMonthPayroll(
        int $userId,
        string $month
    ): ?PayrollRecord {
        $date          = \Carbon\Carbon::createFromFormat('Y-m', $month);
        $previousMonth = $date->subMonth();

        return $this->model
            ->where('user_id', $userId)
            ->whereYear('payroll_month', $previousMonth->year)
            ->whereMonth('payroll_month', $previousMonth->month)
            ->first();
    }

    /**
     * Create late deduction log
     */
    public function createDeductionLog(array $data): LateDeductionLog
    {
        return LateDeductionLog::create($data);
    }

    /**
     * Get quarterly summary for user
     */
    public function getQuarterlySummary(
        int $userId,
        int $year,
        int $quarter
    ): Collection {
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth   = $startMonth + 2;

        return $this->model
            ->where('user_id', $userId)
            ->whereYear('payroll_month', $year)
            ->whereMonth('payroll_month', '>=', $startMonth)
            ->whereMonth('payroll_month', '<=', $endMonth)
            ->orderBy('payroll_month')
            ->get();
    }
}