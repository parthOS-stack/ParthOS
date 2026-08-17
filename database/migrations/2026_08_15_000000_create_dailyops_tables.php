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
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('task_key')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->string('status')->default('todo');
            $table->string('priority')->default('medium');
            
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            
            $table->string('category')->nullable();
            
            $table->boolean('focus_task')->default(false);
            $table->integer('reminder')->nullable();
            $table->boolean('notification_enabled')->default(false);
            
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('priority');
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
        });

        Schema::create('label_task', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('label_id')->constrained('labels')->cascadeOnDelete();
            
            $table->unique(['task_id', 'label_id']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('label_task');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('labels');
    }
};
