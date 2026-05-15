<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar la migración existente
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()
                ->constrained('accounts')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()
                ->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->string('prefix', 3);
            $table->boolean('is_global')->default(false); // true = visible para todas las cuentas
            $table->timestamps();

            $table->index(['account_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
