<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('model_type'); // Task, Board, Budget, etc.
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('action_type'); // created, updated, deleted, status_changed, etc.
            $table->text('description');
            $table->timestamps();

            $table->index(['board_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};