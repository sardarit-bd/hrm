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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client_name');
            $table->text('description')->nullable();
            $table->foreignId('project_manager_id')->constrained('users')->onDelete('restrict');
            $table->enum('type', ['single', 'milestone', 'hourly']);
            $table->decimal('total_budget', 12, 2);
            $table->string('currency', 10);
            $table->decimal('exchange_rate_snapshot', 10, 4);
            $table->date('start_date');
            $table->date('deadline');
            $table->date('delivered_date')->nullable();
            $table->enum('status', ['ongoing', 'delivered', 'delayed', 'cancelled'])->default('ongoing');
            $table->timestamps();

            $table->index('project_manager_id');
            $table->index('status');
            $table->index('type');
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
