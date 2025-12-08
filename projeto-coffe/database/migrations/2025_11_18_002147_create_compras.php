<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('compras', function (Blueprint $table) {
        $table->id();
        $table->foreignId('fila_id')->constrained('fila')->onDelete('cascade')->nullable()->after('usuario_id');
        $table->foreignId('usuario_id')->constrained('usuario')->onDelete('cascade');
        $table->integer('cafe_qtd');
        $table->integer('filtro_qtd');
        $table->dateTime('data_compra');
        $table->foreignId('alterado_por')->nullable()->constrained('usuario');
        $table->dateTime('alterado_em')->nullable();
        $table->timestamps();
        $table->softDeletes();
        
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras',function (Blueprint $table) {
        $table->dropForeign('fila_id');
        $table->dropColumm('fila_id');
        });
    }
};
