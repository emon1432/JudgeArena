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
        Schema::table('standing_task_results', function (Blueprint $table) {
            $table->decimal('points', 20, 2)->nullable()->change();
        });

        Schema::table('standings', function (Blueprint $table) {
            $table->decimal('points', 20, 2)->nullable()->change();
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->decimal('points', 20, 2)->nullable()->change();
        });

        Schema::table('problems', function (Blueprint $table) {
            $table->decimal('points', 20, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('standing_task_results', function (Blueprint $table) {
            $table->decimal('points', 12, 2)->nullable()->change();
        });

        Schema::table('standings', function (Blueprint $table) {
            $table->float('points', 12, 2)->nullable()->change();
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->float('points', 12, 2)->nullable()->change();
        });

        Schema::table('problems', function (Blueprint $table) {
            $table->float('points', 8, 2)->nullable()->change();
        });
    }
};

