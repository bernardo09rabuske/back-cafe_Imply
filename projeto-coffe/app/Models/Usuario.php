<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'usuario';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'acesso',
    ];

    protected $hidden = ['senha'];

    public function compras(): HasMany
    {
        return $this->hasMany(Compras::class, 'usuario_id');
    }

    // Faz o Auth saber que a senha está na coluna 'senha'
    public function getAuthPassword()
    {
        return $this->senha;
    }
}
