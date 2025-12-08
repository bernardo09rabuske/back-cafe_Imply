<?php

namespace App\Http\Controllers;
use App\Models\Fila;
use App\Models\Usuario;
use Illuminate\Http\Request;

class FilaController extends Controller
{
    
    public function listar()
    {
       $fila = Fila::with('usuario')
       ->whereNull('deleted_at')
       ->orderBy('posicao', 'asc')
       ->get();

       return ['message' => 'Listando fila de usuarios', 'fila' => $fila->toArray()];
    }


   public function buscar(Request $request, $id)
{
    
    if (!is_numeric($id)) {
        return response()->json(['message' => 'ID inválido'], 400);
    }

    $filas = Fila::with('usuario')
             ->where('ativo', true)
             ->whereHas('usuario')
             ->orderBy('posicao', 'asc')
             ->get()
             ->values();

    if ($filas->isEmpty()) {
        return response()->json([
            'message' => 'Nenhum registro encontrado para esse usuário.'
        ], 404);
    }

    return response()->json([
        'message' => 'Registro(s) encontrado(s)',
        'data' => $filas
    ]);
}
   public function adicionar(Request $request, $id)
{
    $fila = Usuario::findOrFail($id);

    $existente = Fila::where('usuario_id', $id)
    ->whereNull('deleted_at')
    ->first();

    if($existente){
        return ['message' => 'Usuário ja cadastrado na fila', 'fila' => $existente->toArray()];
    }

    $ultimoFila = Fila::whereNull('deleted_at')
    ->orderBy('posicao', 'desc')
    ->first();

    $posicaoNova = $ultimoFila ? $ultimoFila->posicao + 1 : 1;

    $fila = new Fila();
    $fila->usuario_id = $id;
    $fila->posicao = $posicaoNova;
    $fila->ativo = true;
    $fila->save();

    return ['message' => 'Usuário cadastrado na fila com sucesso', 'fila' => $fila->toArray()];

}
    public function restaurar($id)
    {
        $fila = Fila::withTrashed()->find($id);
        if (!$fila) return response()->json(['error' => 'Registro não encontrado'], 404);

        $fila->restore();

        return response()->json(['message' => 'Registro restaurado com sucesso', 'data' => $fila]);
    }
    public function excluir($id)
    {
        $fila = Fila::find($id);
        if (!$fila) return response()->json(['error' => 'Registro não encontrado'], 404);

        $fila->delete();

        return response()->json(['message' => 'Registro excluído com sucesso', 'id_excluido' => $id]);
    }
    
    public function destruir($id)
    {
        $fila = Fila::withTrashed()->find($id);
        if (!$fila) return response()->json(['error' => 'Registro não encontrado'], 404);

        $fila->forceDelete();

        return response()->json(['message' => 'Registro destruído permanentemente', 'id' => $id]);
    }

    public function moverAposCompra(int $usuarioId)
{
    $filaAtual = Fila::where('usuario_id', $usuarioId)
        ->whereNull('deleted_at')
        ->where('ativo', true)
        ->first();

    if (!$filaAtual) {
        return ['message' => 'Usuario não encontrado na fila'];
    }

    $posicaoAtual = $filaAtual->posicao;


    $filaAtual->delete(); 

    Fila::whereNull('deleted_at')
        ->where('ativo', true)
        ->where('posicao', '>', $posicaoAtual)
        ->decrement('posicao');

    $ultimoFila = Fila::whereNull('deleted_at')
        ->where('ativo', true)
        ->orderBy('posicao', 'desc')
        ->first();

    $posicaoNova = $ultimoFila ? $ultimoFila->posicao + 1 : 1;

    $fila = new Fila();
    $fila->usuario_id = $usuarioId;
    $fila->posicao = $posicaoNova;
    $fila->ativo = true;
    $fila->save();

    return ['message' => 'Usuario mudado com sucesso', 'fila' => $fila->toArray()];
}
}
        
