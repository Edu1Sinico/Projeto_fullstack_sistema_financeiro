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
        Schema::create('transactiongoal', function (Blueprint $table) {
            $table->bigInteger('transactionId')->unsigned()->index()->nullable();
$table->foreign('transactionId')->references('id')->on('Transaction')->onDelete('cascade');

$table->bigInteger('goalId')->unsigned()->index()->nullable();
$table->foreign('goalId')->references('id')->on('Goal')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactiongoal');
    }
};
