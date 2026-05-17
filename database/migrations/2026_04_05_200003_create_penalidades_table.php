<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalidades', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['turma', 'aluno']);
            $table->foreignId('turma_id')->nullable()->constrained('turmas')->onDelete('set null');
            $table->foreignId('aluno_id')->nullable()->constrained('alunos')->onDelete('set null');
            $table->text('motivo');
            $table->integer('pontos');
            $table->foreignId('registrado_por')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Index for querying by subject
            $table->index('turma_id');
            $table->index('aluno_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalidades');
    }
};
