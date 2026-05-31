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
        Schema::create('application_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20);
            $table->string('category', 50)->index();
            $table->string('platform')->nullable()->index();
            $table->string('entity_type')->nullable()->index();
            $table->string('entity_id')->nullable()->index();
            $table->string('message');
            $table->json('context')->nullable();
            $table->string('source', 255)->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['level', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index(['platform', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_logs');
    }
};
