<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goal_transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->decimal('contributed_amount', 15, 2);
            $table->timestamps();

            $table->unique(['goal_id', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_transaction');
    }
};
