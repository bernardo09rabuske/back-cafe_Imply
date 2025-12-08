<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComprasRequest;
use Illuminate\Http\Request;
use App\Models\Compras;
use App\Models\Fila;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComprasController extends Controller
{
   
  public function adicionar(ComprasRequest $request)
{
    $validate = $request->validated();

    $usuarioId = $validate['usuario_id'];

    app(FilaController::class)->moverAposCompra($usuarioId);

    $fila = Fila::where('usuario_id', $usuarioId)
        ->whereNull('deleted_at')
        ->orderBy('posicao', 'asc')
        ->first();

        if (!$fila){
            return ['message' => 'Usuário nao encontrado na fila'];
        }

        $compra = new Compras();
        $compra-> usuario_id = $usuarioId;
        $compra->fila_id = $fila->id;
        $compra->cafe_qtd = $validate['cafe_qtd'];
        $compra->filtro_qtd = $validate['filtro_qtd'];
        $compra->data_compra = now();
        $compra->save();
       return['message'=>'Compra registrada com sucesso', 'compra'=>$compra->toArray()];

}
public function listar(Request $request)
{
     $compras = Compras::get();
        return ['compras' => $compras->toArray()];
    }


    
    public function buscar($usuario_id)
    {
        $compras = Compras::with('usuario')
                    ->where('usuario_id', $usuario_id)
                    ->orderBy('data_compra', 'desc')
                    ->get();

        if ($compras->isEmpty()) {
            return response()->json([
                'message' => 'Nenhuma compra encontrada para esse usuário.'
            ], 404);
        }

        return response()->json([
            'message' => 'Compras encontradas',
            'data' => $compras
        ]);
    }

    
    public function atualizar(Request $request, $id)
    {
        $request->validate([
            'item' => 'string|max:255',
            'quantidade' => 'integer|min:1',
            'alterado_por' => 'exists:usuario,id',
        ]);

        $compra = Compras::findOrFail($id);

        $compra->update([
            'item' => $request->item ?? $compra->item,
            'quantidade' => $request->quantidade ?? $compra->quantidade,
            'alterado_por' => $request->alterado_por ?? $compra->alterado_por,
            'alterado_em' => now(),
        ]);

        return response()->json([
            'message' => 'Compra atualizada com sucesso!',
            'data' => $compra
        ]);
    }

    
    public function excluir($id)
    {
        $compra = Compras::findOrFail($id);
        $compra->delete();

        return response()->json(['message' => 'Compra excluída com sucesso!']);
    }

    
    public function restaurar($id, Request $request)
    {
        $compra = Compras::onlyTrashed()->find($id);

        if (!$compra) {
            return response()->json([
                'message' => 'Compra não encontrada ou não foi deletada.'
            ], 404);
        }

        $compra->restore();

      
        if ($request->filled('alterado_por')) {
            $compra->alterado_por = $request->alterado_por;
            $compra->alterado_em = now();
            $compra->save();
        }

        return response()->json([
            'message' => 'Compra restaurada com sucesso!',
            'data' => $compra
        ]);
    }

    public function cancelar($compraId)
{
    $compra = Compras::findOrFail($compraId);

    
    $filaController = app(FilaController::class);
    $filaAtual = Fila::where('usuario_id', $compra->usuario_id)
                     ->whereNull('deleted_at')
                     ->where('ativo', true)
                     ->first();

    
    $compra->delete(); 

    if ($filaAtual) {
        
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
        $novaPosicao = $ultimoFila ? $ultimoFila->posicao + 1 : 1;

        $novaFila = new Fila();
        $novaFila->usuario_id = $compra->usuario_id;
        $novaFila->posicao = $novaPosicao;
        $novaFila->ativo = true;
        $novaFila->save();
    }

    return response()->json([
        'message' => 'Compra cancelada e fila atualizada com sucesso',
        'compra' => $compra
    ]);
}

    public function dashboard()
    {
        $ultimaCompra = Compras::orderBy('data_compra', 'desc')->first();
        $tempoDesdeUltima = $ultimaCompra
            ? Carbon::parse($ultimaCompra->data_compra)->diffForHumans()
            : 'Nenhuma compra registrada.';

        $resumo = [
            'tempo_desde_ultima' => $tempoDesdeUltima,
            'resumo_ultimas' => Compras::with('usuario')
                ->orderBy('data_compra', 'desc')
                ->take(5)
                ->get(),
            'compras_por_usuario' => Compras::selectRaw('usuario_id, COUNT(*) as total')
                ->groupBy('usuario_id')
                ->with('usuario')
                ->get(),
        ];

        return response()->json($resumo);
    }
}
