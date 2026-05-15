<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('type'); // 'sale', 'rental', 'sale_return', 'rental_return', 'rental_cancel', 'late_fee', 'product_return', 'other_income', 'other_expense'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable(); // 'Sale', 'Rental', etc.
            $table->decimal('amount', 10, 2);
            $table->enum('direction', ['in', 'out']);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['account_id', 'created_at']);
            $table->index(['type']);
            $table->index(['direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
