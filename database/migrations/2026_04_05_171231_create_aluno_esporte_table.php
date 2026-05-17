<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aluno_esporte', function (Blueprint $table) {
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->foreignId('esporte_id')->constrained('esportes')->onDelete('cascade');
            
            $table->primary(['aluno_id', 'esporte_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_esporte');
    }
};
