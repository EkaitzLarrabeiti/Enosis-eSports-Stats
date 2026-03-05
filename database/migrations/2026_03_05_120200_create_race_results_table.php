<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subsession_id')->index();
            $table->string('series_name')->nullable();
            $table->string('track_name')->nullable();
            $table->unsignedInteger('finish_position')->nullable();
            $table->unsignedInteger('starting_position')->nullable();
            $table->unsignedInteger('incidents')->nullable();
            $table->integer('irating_change')->nullable();
            $table->dateTime('race_date')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_results');
    }
};
