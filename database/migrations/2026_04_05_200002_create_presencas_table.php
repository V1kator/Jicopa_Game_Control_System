<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presencas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jogo_id')->constrained('jogos')->onDelete('cascade');
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->boolean('presente')->default(true);
            $table->boolean('is_substituto')->default(false);
            $table->timestamps();

            // Unique constraint: one presence record per athlete per game
            $table->unique(['jogo_id', 'aluno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presencas');
    }
};
