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
    Schema::create('fila', function (Blueprint $table) {
        $table->id();
        $table->foreignId('usuario_id')->constrained('usuario')->onDelete('cascade');
        $table->integer('posicao');
        $table->boolean('ativo')->default(true);
        $table->timestamps();
        $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fila');
    }
};
