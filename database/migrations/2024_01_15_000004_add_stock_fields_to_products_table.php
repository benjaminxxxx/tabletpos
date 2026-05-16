<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'quantity_available')) {
                $table->integer('quantity_available')->default(1)->after('stock_quantity');
            }
            if (!Schema::hasColumn('products', 'quantity_rented_out')) {
                $table->integer('quantity_rented_out')->default(0)->after('quantity_available');
            }
            if (!Schema::hasColumn('products', 'quantity_sold_total')) {
                $table->integer('quantity_sold_total')->default(0)->after('quantity_rented_out');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'quantity_available')) {
                $table->dropColumn('quantity_available');
            }
            if (Schema::hasColumn('products', 'quantity_rented_out')) {
                $table->dropColumn('quantity_rented_out');
            }
            if (Schema::hasColumn('products', 'quantity_sold_total')) {
                $table->dropColumn('quantity_sold_total');
            }
        });
    }
};
