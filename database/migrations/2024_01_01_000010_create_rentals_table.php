<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('public_code_snapshot');
            $table->text('description_snapshot')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->date('return_date');
            $table->date('returned_at')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue', 'cancelled'])->default('active');
            $table->timestamps();
            
            $table->index(['account_id', 'customer_id']);
            $table->index(['account_id', 'status']);
            $table->index(['return_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
