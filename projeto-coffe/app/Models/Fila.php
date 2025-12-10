<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fila extends Model
{
    use SoftDeletes;

    protected $table = 'fila';
    protected $fillable = [
        'usuario_id',
        'posicao',
        'ativo',
    ];


    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    
    public static function usuarioDaVez()
    {
        return self::where('ativo', true)->orderBy('posicao', 'asc')->first();
    }

    
    public static function avancarFila()
    {
        $primeiro = self::usuarioDaVez();
        if ($primeiro) {
            $primeiro->delete(); 
        }

        $filas = self::where('ativo', true)->orderBy('posicao', 'asc')->get();
        foreach ($filas as $index => $fila) {
            $fila->posicao = $index + 1;
            $fila->save();
        }
    }
    public function compra() {
    return $this->belongsTo(Compras::class, 'compra_id');
}
}
