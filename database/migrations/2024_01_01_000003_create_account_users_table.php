<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['admin', 'seller'])->default('seller');
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
            // Sin softDeletes: si se elimina la membresía, se elimina.
            // El historial de acciones del usuario vive en las tablas de ventas/audit.

            $table->unique(['account_id', 'user_id']);
            $table->index('account_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_users');
    }
};
