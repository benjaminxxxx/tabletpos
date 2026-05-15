<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_closes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('close_date');
            $table->decimal('expected_amount', 10, 2);
            $table->decimal('confirmed_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['account_id', 'close_date']);
            $table->unique(['account_id', 'close_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_closes');
    }
};
