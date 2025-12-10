<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComprasRequest;
use Illuminate\Http\Request;
use App\Models\Compras;
use App\Models\Fila;
use Carbon\Carbon;

class ComprasController extends Controller
{
    // ---------------------------
    // Função auxiliar admin
    // ---------------------------
    private function checkAdmin(Request $request)
    {
        $isAdmin = $request->header('X-User-Admin');
        if (!$isAdmin || $isAdmin !== 'true') {
            response()->json(['error' => 'Acesso negado'], 403)->send();
            exit;
        }
    }

    // ---------------------------
    // Adicionar compra
    // ---------------------------
    public function adicionar(ComprasRequest $request)
    {
        $validate = $request->validated();
        $usuarioId = $validate['usuario_id'];

        app(FilaController::class)->moverAposCompra($usuarioId);

        $fila = Fila::where('usuario_id', $usuarioId)
                    ->whereNull('deleted_at')
                    ->orderBy('posicao', 'asc')
                    ->first();

        if (!$fila) {
            return response()->json(['message' => 'Usuário não encontrado na fila'], 404);
        }

        $compra = new Compras();
        $compra->usuario_id = $usuarioId;
        $compra->fila_id = $fila->id;
        $compra->cafe_qtd = $validate['cafe_qtd'];
        $compra->filtro_qtd = $validate['filtro_qtd'];
        $compra->data_compra = now();
        $compra->save();

        return response()->json(['message' => 'Compra registrada com sucesso', 'compra' => $compra]);
    }

    // ---------------------------
    // Listar todas as compras
    // ---------------------------
    public function listar(Request $request)
    {
        $compras = Compras::get();
        return response()->json(['compras' => $compras]);
    }

    // ---------------------------
    // Cancelar compra (somente admin)
    // ---------------------------
    public function cancelar($compraId, Request $request)
    {
        $this->checkAdmin($request);

        $compra = Compras::findOrFail($compraId);

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

    // ---------------------------
    // Dashboard admin (somente admin)
    // ---------------------------
    public function dashboard(Request $request)
{
    $this->checkAdmin($request);

    $page = $request->query('page', 1);
    $perPage = 10;

    $comprasQuery = Compras::with('usuario')->orderBy('data_compra', 'desc');
    $total = $comprasQuery->count();

    $compras = $comprasQuery->skip(($page - 1) * $perPage)->take($perPage)->get();

    $resumo = [
        'total' => $total,
        'resumo_ultimas' => $compras,
        'compras_por_usuario' => Compras::selectRaw('usuario_id, COUNT(*) as total')
            ->groupBy('usuario_id')
            ->with('usuario')
            ->get(),
    ];

    return response()->json($resumo);
}
}
