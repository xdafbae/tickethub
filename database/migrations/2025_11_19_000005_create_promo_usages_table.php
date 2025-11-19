<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('promo_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_id')->constrained('promos')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('session_id')->nullable(); // fallback jika user belum login
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();

            $table->index(['promo_id', 'user_id']);
            $table->index(['promo_id', 'session_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('promo_usages');
    }
};