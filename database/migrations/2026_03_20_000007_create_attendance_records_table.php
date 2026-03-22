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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('roster_assignment_id')->constrained('roster_assignments')->onDelete('restrict');
            $table->foreignId('policy_id')->constrained('attendance_policies')->onDelete('restrict');
            $table->date('date');
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->time('expected_check_in')->nullable();
            $table->time('expected_check_out')->nullable();
            $table->decimal('working_hours', 4, 2)->nullable();
            $table->integer('late_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->boolean('is_within_grace_period')->default(false);
            $table->enum('status', [
                'present',
                'absent',
                'late',
                'half_day',
                'on_leave',
                'weekend',
                'holiday'
            ]);
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index('date');
            $table->index('status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
