<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->string('google_id')->nullable()->unique();
            $table->string('profile_photo_path', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
            $table->dropColumn('google_id');
            $table->dropColumn('profile_photo_path');
        });
    }
};
