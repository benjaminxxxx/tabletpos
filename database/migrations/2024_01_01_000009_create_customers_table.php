<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->string('dni')->nullable();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('dni_photo_path')->nullable();
            $table->string('selfie_photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['account_id', 'dni']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
