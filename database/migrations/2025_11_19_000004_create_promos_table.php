<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percent', 'nominal']);
            $table->unsignedInteger('value'); // percent 1..100 atau nominal dalam rupiah
            $table->unsignedInteger('usage_limit_total')->nullable(); // batas total (null = tanpa batas)
            $table->unsignedInteger('usage_limit_per_user')->nullable(); // batas per user (null = tanpa batas)
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('promos');
    }
};