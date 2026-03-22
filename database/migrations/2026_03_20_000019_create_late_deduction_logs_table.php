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
        Schema::create('late_deduction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_record_id')->constrained('payroll_records')->onDelete('cascade');
            $table->enum('deduction_type', ['late', 'absent', 'half_day']);
            $table->date('reference_date');
            $table->decimal('deduction_amount', 10, 2);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('payroll_record_id');
            $table->index('deduction_type');
            $table->index('reference_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('late_deduction_logs');
    }
};
