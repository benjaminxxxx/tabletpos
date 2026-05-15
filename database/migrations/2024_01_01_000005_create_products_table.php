<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->string('public_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('origin')->nullable();
            $table->char('category_prefix', 2);
            $table->enum('status', ['available', 'rented', 'laundry', 'maintenance', 'blocked'])->default('available');
            $table->boolean('can_sell')->default(true);
            $table->boolean('can_rent')->default(true);
            $table->integer('rent_count')->default(0);
            $table->integer('sale_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
