<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anonymous_feedbacks', function (Blueprint $table) {
            $table->foreignId('topic_id')
                ->nullable()
                ->after('id')
                ->constrained('topics')
                ->onDelete('restrict');
        });

        $defaultTopics = [
            'work_environment' => 'Work Environment',
            'management'       => 'Management',
            'process'          => 'Process',
            'compensation'     => 'Compensation',
            'other'            => 'Other',
        ];

        $topicIdBySlug = [];

        foreach ($defaultTopics as $slug => $name) {
            $existingId = DB::table('topics')->where('slug', $slug)->value('id');

            if (!$existingId) {
                $existingId = DB::table('topics')->insertGetId([
                    'name'       => $name,
                    'slug'       => $slug,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $topicIdBySlug[$slug] = $existingId;
        }

        $usedCategories = DB::table('anonymous_feedbacks')
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter();

        foreach ($usedCategories as $category) {
            $slug = Str::slug((string) $category, '_');

            if (!isset($topicIdBySlug[$slug])) {
                $topicIdBySlug[$slug] = DB::table('topics')->insertGetId([
                    'name'       => Str::of((string) $category)->replace('_', ' ')->title()->toString(),
                    'slug'       => $slug,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('anonymous_feedbacks')
                ->where('category', $category)
                ->update(['topic_id' => $topicIdBySlug[$slug]]);
        }

        Schema::table('anonymous_feedbacks', function (Blueprint $table) {
            $table->unsignedBigInteger('topic_id')->nullable(false)->change();
            $table->dropColumn('category');

            $table->index('topic_id');
            $table->index('quarter');
            $table->index('sentiment');
            $table->index('created_at');
            $table->index(['topic_id', 'quarter', 'sentiment', 'created_at'], 'anon_feedback_topic_quarter_sentiment_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('anonymous_feedbacks', function (Blueprint $table) {
            $table->enum('category', [
                'work_environment',
                'management',
                'process',
                'compensation',
                'other',
            ])->after('id');
        });

        $topicNames = DB::table('topics')->pluck('name', 'id');

        foreach ($topicNames as $topicId => $name) {
            $fallbackCategory = Str::slug((string) $name, '_');

            DB::table('anonymous_feedbacks')
                ->where('topic_id', $topicId)
                ->update(['category' => $fallbackCategory]);
        }

        Schema::table('anonymous_feedbacks', function (Blueprint $table) {
            $table->dropIndex(['topic_id']);
            $table->dropIndex(['quarter']);
            $table->dropIndex(['sentiment']);
            $table->dropIndex(['created_at']);
            $table->dropIndex('anon_feedback_topic_quarter_sentiment_created_idx');
            $table->dropForeign(['topic_id']);
            $table->dropColumn('topic_id');
        });
    }
};
