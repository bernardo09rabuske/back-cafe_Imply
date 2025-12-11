<?php

namespace App\Http\Controllers;

use App\Models\Fila;
use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Models\Compras;

class FilaController extends Controller
{

    private function checkAdmin(Request $request)
    {
        $isAdmin = $request->header('X-User-Admin');
        if (!$isAdmin || $isAdmin !== 'true') {
            response()->json(['error' => 'Acesso negado'], 403)->send();
            exit;
        }
    }

  public function listar()
{
    $fila = Fila::with('usuario')
        ->whereNull('deleted_at')
        ->orderBy('posicao', 'asc')
        ->get();

    return response()->json(['fila' => $fila]);
}


    public function adicionar(Request $request)
{
    $user = $request->user();

    $ja = Fila::where('usuario_id', $user->id)->whereNull('deleted_at')->first();
    if ($ja) return response()->json(['message' => 'Já está na fila'], 400);

    $compra = Compras::create([
        'usuario_id' => $user->id,
        'cafe_qtd' => $request->cafe_qtd ?? 1,
        'filtro_qtd' => $request->filtro_qtd ?? 0,
        'data_compra' => now()
    ]);

    $ultimo = Fila::whereNull('deleted_at')->orderBy('posicao', 'desc')->first();
    $pos = $ultimo ? $ultimo->posicao + 1 : 1;

    $fila = Fila::create([
        'usuario_id' => $user->id,
        'compra_id' => $compra->id,
        'posicao' => $pos,
        'ativo' => true
    ]);

    return ['message' => 'Entrou na fila', 'fila' => $fila];
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
    public function excluir($usuarioId)
{
    $fila = Fila::where('usuario_id', $usuarioId)->whereNull('deleted_at')->first();
    if (!$fila) {
        return response()->json(['error' => 'Usuário não está na fila'], 404);
    }

    $fila->delete();

    return response()->json(['message' => 'Saiu da fila']);
}


    public function moverAposCompra(int $usuarioId)
{
    $filaAtual = Fila::where('usuario_id', $usuarioId)
        ->whereNull('deleted_at')
        ->where('ativo', true)
        ->first();

    if (!$filaAtual) return ['message' => 'Usuário não encontrado na fila'];

    $posicaoAtual = $filaAtual->posicao;
    $filaAtual->delete();

    // Recalcula posições dos que estavam atrás
    Fila::whereNull('deleted_at')
        ->where('ativo', true)
        ->where('posicao', '>', $posicaoAtual)
        ->decrement('posicao');

    // Coloca no final
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

    return ['message' => 'Usuário movido com sucesso', 'fila' => $fila];
}

public function sair(Request $request)
{
    $user = $request->user();

    $fila = Fila::where('usuario_id', $user->id)->whereNull('deleted_at')->first();
    if (!$fila) return response()->json(['error' => 'Não está na fila'], 404);

    $fila->delete();

    return ['message' => 'Saiu da fila'];
}

}

 