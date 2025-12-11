<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emails', function (Blueprint $table) {
            if (!Schema::hasColumn('emails', 'is_focused')) {
                $table->boolean('is_focused')->default(false)->after('is_flagged');
            }
            if (!Schema::hasColumn('emails', 'importance_score')) {
                $table->decimal('importance_score', 5, 2)->default(0)->after('is_focused');
            }
            if (!Schema::hasColumn('emails', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('importance_score');
            }
            if (!Schema::hasColumn('emails', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable()->after('is_pinned');
            }
            
            // Add indexes for performance
            $table->index(['recipient_id', 'folder', 'is_focused']);
            $table->index(['is_pinned', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['recipient_id', 'folder', 'is_focused']);
            $table->dropIndex(['is_pinned', 'created_at']);
            
            // Drop columns
            $table->dropColumn(['is_focused', 'importance_score', 'is_pinned', 'pinned_at']);
        });
    }
};