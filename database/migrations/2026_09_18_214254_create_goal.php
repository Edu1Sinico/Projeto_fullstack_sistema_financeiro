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
        Schema::create('Goal', function (Blueprint $table) {
 $table->id();
$table->string('title');
    $table->decimal('totalAmount',total: 8, places: 2);

$table->decimal('currentAmount',total: 8, places: 2);
    $table->bigInteger('userId')->unsigned()->index()->nullable();
$table->foreign('userId')->references('id')->on('User')->onDelete('cascade');
$table->date('deadline');
$table->boolean('completed');

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal');
    }
};
