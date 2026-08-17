<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->index();
            $table->string('party_name');
            $table->decimal('amount', 12, 2);
            $table->string('type', 16); // receivable | payable
            $table->string('category')->nullable();
            $table->date('transaction_date');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'type', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
