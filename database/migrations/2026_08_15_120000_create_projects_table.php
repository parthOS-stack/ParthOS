<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('key');
            $table->text('description')->nullable();
            $table->string('status')->default('planning');
            $table->string('priority')->default('medium');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'key']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'priority']);
            $table->index(['user_id', 'due_date']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('project_id')
                ->nullable()
                ->after('user_id')
                ->constrained('projects')
                ->nullOnDelete();

            $table->index(['user_id', 'project_id']);
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropIndex(['user_id', 'project_id']);
            $table->dropIndex(['project_id', 'status']);
            $table->dropColumn('project_id');
        });

        Schema::dropIfExists('projects');
    }
};
