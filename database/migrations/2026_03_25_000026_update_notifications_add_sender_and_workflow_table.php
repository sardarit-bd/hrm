<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('sender_user_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('sender_type', ['system', 'user'])
                ->default('system')
                ->after('sender_user_id');

            $table->enum('delivery_type', ['system', 'workflow', 'custom'])
                ->default('system')
                ->after('type');

            $table->string('module', 50)->nullable()->after('delivery_type');
            $table->string('entity_type', 100)->nullable()->after('module');
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
            $table->unsignedTinyInteger('workflow_step')->nullable()->after('entity_id');
            $table->string('workflow_stage', 50)->nullable()->after('workflow_step');
            $table->json('context')->nullable()->after('workflow_stage');
            $table->dateTime('delivered_at')->nullable()->after('context');

            $table->index('sender_user_id');
            $table->index(['user_id', 'is_read', 'created_at'], 'notifications_user_read_created_idx');
            $table->index(['module', 'entity_type', 'entity_id'], 'notifications_entity_idx');
            $table->index(['delivery_type', 'workflow_stage'], 'notifications_delivery_stage_idx');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_read_created_idx');
            $table->dropIndex('notifications_entity_idx');
            $table->dropIndex('notifications_delivery_stage_idx');
            $table->dropIndex(['sender_user_id']);

            $table->dropForeign(['sender_user_id']);
            $table->dropColumn([
                'sender_user_id',
                'sender_type',
                'delivery_type',
                'module',
                'entity_type',
                'entity_id',
                'workflow_step',
                'workflow_stage',
                'context',
                'delivered_at',
            ]);
        });
    }
};
