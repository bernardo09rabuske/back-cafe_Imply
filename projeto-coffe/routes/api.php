<?php

use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\FilaController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\LoginController;



use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    //return json_encode(['message => 'API laravel]);
    return ['message' => 'API laravel'];
});


Route::prefix('usuario')->group(function () {
    Route::post('', [UsuarioController::class, 'postar']);
    Route::get('', [UsuarioController::class, 'listar']);
    Route::get('{id}', [UsuarioController::class, 'buscar']);
    Route::put('{id}', [UsuarioController::class, 'atualizar']);
    Route::post('{id}', [UsuarioController::class, 'restaurar']);
    Route::delete('excluir/{id}', [UsuarioController::class, 'excluir']);
    Route::delete('destruir/{id}', [UsuarioController::class, 'destroyer']);
});
Route::prefix('fila')->group(function () {
    Route::post('{id}', [FilaController::class, 'adicionar']);
    Route::get('', [FilaController::class, 'listar']);
    Route::get('{id}', [FilaController::class, 'buscar']);
    Route::post('restaurar/{id}', [FilaController::class, 'restaurar']);
    Route::delete('excluir/{id}', [FilaController::class, 'excluir']);
    Route::delete('destruir/{id}', [FilaController::class, 'destruir']);
});


Route::prefix('compras')->group(function () {
    Route::post('', [ComprasController::class, 'adicionar']);
    Route::get('', [ComprasController::class, 'listar']);
    Route::post('restaurar/{id}', [ComprasController::class, 'restaurar']);
    Route::get('{id}', [ComprasController::class, 'buscar']);
    Route::put('{id}', [ComprasController::class, 'atualizar']);
    Route::delete('cancelar/{id}', [ComprasController::class, 'cancelar']);
    Route::delete('excluir/{id}', [ComprasController::class, 'excluir']);
    Route::delete('destruir/{id}', [ComprasController::class, 'destruir']);
});


Route::prefix('auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/revoke-all', [LoginController::class, 'revokeAllTokens'])->middleware('auth:sanctum');
    Route::get('/check-ability/{ability}', [LoginController::class, 'checkAbility'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->get('/dashboard', function () {
    return 'Área protegida!';
});
 
Route::middleware('auth:sanctum')->post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken(
        $request->token_name,     
        $request->abilities ?? []  
    );

    return response()->json([
        'token' => $token->plainTextToken
    ]);
});

Route::middleware('auth:sanctum')->post('/tokens/revoke-all', function (Request $request) {
    $request->user()->tokens()->delete();

    return response()->json(['message' => 'Todos os tokens foram revogados']);
});

Route::middleware('auth:sanctum')->get('/tokens', function (Request $request) {
    return $request->user()->tokens;
});

Route::middleware(['auth:sanctum'])->post('/admin/update', function (Request $request) {

    if ($request->user()->tokenCan('server:update')) {
        return "Pode atualizar o servidor";
    }

    return response()->json(['error' => 'Token sem permissão'], 403);
});


Route::delete('/fila/{id}', [FilaController::class, 'excluir'])->middleware('admin');
Route::post('/compras/{id}/cancelar', [ComprasController::class, 'cancelar'])->middleware('admin');
Route::get('/dashboard/admin', [ComprasController::class, 'dashboard'])->middleware('admin');
