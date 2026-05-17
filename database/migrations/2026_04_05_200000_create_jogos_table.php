<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jogos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('esporte_id')->constrained('esportes')->onDelete('cascade');
            $table->foreignId('time1_id')->nullable()->constrained('turmas')->onDelete('set null');
            $table->foreignId('time2_id')->nullable()->constrained('turmas')->onDelete('set null');
            $table->date('data');
            $table->time('hora');
            $table->string('local', 255);
            $table->integer('placar_time1')->nullable();
            $table->integer('placar_time2')->nullable();
            $table->foreignId('vencedor_id')->nullable()->constrained('turmas')->onDelete('set null');
            $table->boolean('cancelado')->default(false);
            $table->timestamps();

            // Index for conflict detection and filtering
            $table->index(['data', 'hora', 'local']);
            $table->index(['categoria_id', 'esporte_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jogos');
    }
};
