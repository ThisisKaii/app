<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tasks', 'group_id')) {
            try {
                Schema::table('tasks', function (Blueprint $table) {
                    $table->dropForeign(['group_id']);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('tasks', function (Blueprint $table) {
                    $table->dropColumn('group_id');
                });
            } catch (\Exception $e) {}
        }
    }

    public function down(): void
    {
        // No op
    }
};
