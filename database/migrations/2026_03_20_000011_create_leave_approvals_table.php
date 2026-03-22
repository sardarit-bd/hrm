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
        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('restrict');
            $table->enum('approver_role', ['project_manager', 'general_manager']);
            $table->enum('action', ['approved', 'rejected']);
            $table->text('remarks')->nullable();
            $table->dateTime('acted_at');

            $table->index('leave_request_id');
            $table->index('approver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_approvals');
    }
};
