<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_esporte', function (Blueprint $table) {
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('esporte_id')->constrained('esportes')->onDelete('cascade');
            
            $table->primary(['categoria_id', 'esporte_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_esporte');
    }
};
