<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('penalidades', function (Blueprint $table) {
            $table->foreignId('jogo_id')->nullable()->after('tipo')->constrained('jogos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalidades', function (Blueprint $table) {
            $table->dropForeign(['jogo_id']);
            $table->dropColumn('jogo_id');
        });
    }
};
