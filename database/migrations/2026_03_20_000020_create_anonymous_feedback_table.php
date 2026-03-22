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
        Schema::create('anonymous_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->enum('category', [
                'work_environment',
                'management',
                'process',
                'compensation',
                'other'
            ]);
            $table->text('message');
            $table->enum('sentiment', ['positive', 'neutral', 'negative']);
            $table->string('quarter', 10);
            $table->date('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anonymous_feedback');
    }
};
