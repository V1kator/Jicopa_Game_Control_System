<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacao_config', function (Blueprint $table) {
            $table->id();
            $table->integer('num_jurados');
            $table->decimal('nota_min', 5, 2);
            $table->decimal('nota_max', 5, 2);
            $table->integer('pontos_bonus_melhor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacao_config');
    }
};
