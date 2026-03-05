<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('irating')->nullable();
            $table->string('safety_rating')->nullable();
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('podiums')->default(0);
            $table->unsignedInteger('races')->default(0);
            $table->unsignedInteger('poles')->default(0);
            $table->string('favorite_category')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_stats');
    }
};
