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
        Schema::create('zk_punch_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('zk_uid');
            $table->string('employee_code');
            $table->integer('state')->nullable();
            $table->dateTime('punch_time');
            $table->enum('punch_type', ['entry', 'exit']);
            $table->string('device_id');
            $table->dateTime('synced_at');
            $table->boolean('is_processed')->default(false);

            $table->index('employee_code');
            $table->index('punch_time');
            $table->index('is_processed');
            $table->index(['employee_code', 'punch_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zk_punch_logs');
    }
};
