<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('attendance_policy_id')->constrained('attendance_policies')->onDelete('restrict');
            $table->date('payroll_month');
            $table->decimal('basic_salary', 10, 2);
            $table->integer('total_working_days');
            $table->integer('days_present');
            $table->integer('days_absent');
            $table->integer('late_count')->default(0);
            $table->integer('late_carry_forward_in')->default(0);
            $table->integer('late_carry_forward_out')->default(0);
            $table->decimal('late_deduction_days', 4, 2)->default(0);
            $table->decimal('late_deduction_amount', 10, 2)->default(0);
            $table->decimal('absent_deduction_amount', 10, 2)->default(0);
            $table->integer('grace_period_used');
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->enum('payroll_status', ['draft', 'approved', 'paid'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->dateTime('paid_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'payroll_month']);
            $table->index('payroll_month');
            $table->index('payroll_status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
