<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Usuario extends Model{

    use SoftDeletes;
    protected $table = 'usuario';
    protected $hidden = ['senha'];
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'acesso',
    ];
    public function compras()
    {
        return $this->hasMany(Compras::class, 'usuario_id');
    }
}
