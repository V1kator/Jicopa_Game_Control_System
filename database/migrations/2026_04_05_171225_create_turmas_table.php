<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turmas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->enum('period', ['Matutino', 'Vespertino']);
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            // Unique constraint: same name + period cannot exist twice
            $table->unique(['name', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turmas');
    }
};
