<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('customer_id');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_rental_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('guarantee_amount', 10, 2)->nullable();
            $table->string('dni_number');
            $table->string('dni_photo_url');
            $table->string('additional_photo_url')->nullable();
            $table->date('rental_start_date');
            $table->date('rental_return_date');
            $table->text('observations')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->string('product_status_after')->default('alquilado');
            $table->string('status')->default('pendiente');  // Enum: pendiente, activo, devuelto, perdido, vencido
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
            $table->index('sale_id');
            $table->index('product_id');
            $table->index('customer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_details');
    }
};
