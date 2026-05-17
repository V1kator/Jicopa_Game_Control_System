<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presencas', function (Blueprint $table) {
            $table->foreignId('substituto_de_time_id')
                  ->nullable()
                  ->after('is_substituto')
                  ->constrained('turmas')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('presencas', function (Blueprint $table) {
            $table->dropForeign(['substituto_de_time_id']);
            $table->dropColumn('substituto_de_time_id');
        });
    }
};
