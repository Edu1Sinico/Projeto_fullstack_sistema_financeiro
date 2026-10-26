<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('type');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('type');
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('theme', 20)->default('light');
            $table->json('goal_order')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
