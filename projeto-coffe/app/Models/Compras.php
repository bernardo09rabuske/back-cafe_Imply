<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compras extends Model
{
    use SoftDeletes;

    protected $table = 'compras';

    protected $fillable = [
        'usuario_id',
        'fila_id',
        'cafe_qtd',
        'filtro_qtd',
        'data_compra',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function alterador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'alterado_por');
    }

    public function fila(): BelongsTo
    {
        return $this->belongsTo(Fila::class, 'fila_id');
    }
}
