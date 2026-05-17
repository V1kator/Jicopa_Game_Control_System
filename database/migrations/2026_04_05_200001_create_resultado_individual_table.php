<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultado_individual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jogo_id')->constrained('jogos')->onDelete('cascade');
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->integer('posicao');
            $table->timestamps();

            // Index for querying results by game
            $table->index('jogo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultado_individual');
    }
};
