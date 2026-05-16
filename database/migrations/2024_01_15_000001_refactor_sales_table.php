<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Eliminar columnas viejas si existen
            if (Schema::hasColumn('sales', 'product_id')) {
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('sales', 'customer_id')) {
                $table->dropColumn('customer_id');
            }
            if (Schema::hasColumn('sales', 'transaction_type')) {
                $table->dropColumn('transaction_type');
            }
            if (Schema::hasColumn('sales', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('sales', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
            if (Schema::hasColumn('sales', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
            if (Schema::hasColumn('sales', 'rental_price')) {
                $table->dropColumn('rental_price');
            }
            if (Schema::hasColumn('sales', 'deposit_amount')) {
                $table->dropColumn('deposit_amount');
            }
            if (Schema::hasColumn('sales', 'rental_start_date')) {
                $table->dropColumn('rental_start_date');
            }
            if (Schema::hasColumn('sales', 'rental_return_date')) {
                $table->dropColumn('rental_return_date');
            }
            if (Schema::hasColumn('sales', 'rental_observations')) {
                $table->dropColumn('rental_observations');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            // Nuevas columnas
            $table->string('transaction_number')->unique();  // '#1', '#2'
            $table->timestamp('transaction_date')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('pendiente');  // Enum: pendiente, completada, cancelada
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['transaction_number', 'transaction_date', 'total_amount', 'status', 'notes']);
        });
    }
};
