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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('path');
            $table->string('path_thumb')->nullable();  // ~150px ancho
            $table->string('path_full')->nullable();
            $table->string('disk')->default('public');
            $table->string('type')->default('photo');     // photo, video, 3d_model
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('original_name')->nullable();

            // Atributos para matching dinámico — sin FK, string libre
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('color')->nullable();
            $table->string('brand')->nullable();
            $table->string('material')->nullable();
            $table->string('gender')->nullable();         // MASCULINO, FEMENINO, UNISEX

            // Aprobación — vendedores suben en pending, admin/owner aprueban
            $table->string('status')->default('approved'); // approved, pending, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'category_id', 'color', 'brand']);
        });

        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'media_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('media');
    }
};
