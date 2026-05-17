<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('turma_id')->constrained('turmas')->onDelete('cascade');
            $table->enum('period', ['Matutino', 'Vespertino']);
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            // Index for filtering by turma and period
            $table->index(['turma_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};
