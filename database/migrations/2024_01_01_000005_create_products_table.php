<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
            $table->enum('status', ['available', 'rented', 'laundry', 'maintenance', 'blocked'])->default('available');
            $table->boolean('can_sell')->default(true);
            $table->boolean('can_rent')->default(true);
            $table->integer('rent_count')->default(0);
            $table->integer('sale_count')->default(0);

            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();       // talla: S, M, L, XL, 38, etc
            $table->string('material')->nullable();
            $table->string('location_name')->nullable(); // string libre además del FK
            $table->unsignedInteger('stock')->default(1);

            // product_type controla la lógica de negocio:
            // sellable  → seguimiento por código, puede venderse
            // rentable  → seguimiento por código, puede alquilarse
            // stock_only → maneja stock numérico, sin código único relevante
            // asset     → gasto/inversión, no vende ni alquila (maniquí, stand, etc)
            $table->string('product_type')->default('sellable');

            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('gender', ['m', 'f', 'u'])->default('u');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');

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
