<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacao_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turma_id')->constrained('turmas')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->integer('jurado_num');
            $table->decimal('nota', 5, 2);
            $table->timestamps();

            // Unique constraint: one score per turma/categoria/jurado combination
            $table->unique(['turma_id', 'categoria_id', 'jurado_num']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacao_notas');
    }
};
