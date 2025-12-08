<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioRequest;
use Illuminate\Http\Request;
use App\Models\Usuario;

class UsuarioController {
    public function listar(Request $request) {
        
        $usuarios = Usuario::get();
        return ['usuarios' => $usuarios->toArray()];
    }
    public function postar(UsuarioRequest $request)
{
    $validado = $request->all();
    $senha = $validado['senha'];

  
    $regex = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^\w\s])[^\s]{8,}$/';

    
    if (!preg_match($regex, $senha)) {
        return response()->json([
            'error' => 'A senha deve conter pelo menos:
            - 1 letra maiúscula,
            - 1 letra minúscula,
            - 1 número,
            - 1 caractere especial,
            - e ter pelo menos 8 caracteres.'
        ], 400);
    }

    $usuario = new Usuario();
    $usuario->nome = $validado['nome'];
    $usuario->email = $validado['email'];
    $usuario->senha = password_hash($senha, PASSWORD_DEFAULT);

    $usuario->save();

    return response()->json([
        'message' => 'Usuário criado com sucesso!',
        'usuario' => $usuario->toArray()
    ], 201);
}
    public function buscar($id) {
        $usuario = Usuario::find($id);
        return ['usuario' => $usuario->toArray()];
    }
    public function atualizar(UsuarioRequest $request, $id)
{
    $usuario = Usuario::find($id);

    if (!$usuario) {
        return response()->json(['error' => 'Usuário não encontrado.'], 404);
    }

    $validado = $request->validated();

    $usuario->nome = $validado['nome'];
    $usuario->email = $validado['email'];

    if (!empty($validado['senha'])) {
        $usuario->senha = password_hash($validado['senha'], PASSWORD_DEFAULT);
    }

    $usuario->save();

    return response()->json([
        'message' => 'Usuário atualizado com sucesso!',
        'usuario' => $usuario->toArray()
    ], 200);
}

    public function restaurar(int $id) {

        $usuario = Usuario::withTrashed()->find($id);
        $usuario->restore();
        return ['message' => 'Restaurado com sucesso' . $id];
    }
    public function excluir(int $id) {

        $usuario = Usuario::findorfail($id);
        $usuario->delete();

        return ['message' => 'Excluido com sucesso' . $id];
    }
    public function destroyer(int $id)
{
    $destruir = Usuario::withTrashed()->rfind($id);
    $destruir->forceDelete($id);

        return ['message' => 'Destruido com sucesso' . $id];
}
}
