<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scoring_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('points_per_win')->default(3);
            $table->unsignedSmallInteger('points_per_draw')->default(1);
            $table->unsignedSmallInteger('points_per_extra')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoring_configs');
    }
};
