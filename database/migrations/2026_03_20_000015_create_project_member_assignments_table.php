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
        Schema::create('project_member_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_project_assignment_id')->constrained('team_project_assignments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('restrict');
            $table->date('assigned_at');
            $table->date('released_at')->nullable();
            $table->timestamps();

            $table->unique(['team_project_assignment_id', 'user_id'], 'pma_tpa_id_user_id_unique');
            $table->index('team_project_assignment_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_member_assignments');
    }
};
