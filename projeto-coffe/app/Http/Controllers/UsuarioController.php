<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioRequest;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Fila;

class UsuarioController
{
    private function checkAdmin(Request $request)
    {
        $isAdmin = $request->header('X-User-Admin');
        if (!$isAdmin || $isAdmin !== 'true') {
            response()->json(['error' => 'Acesso negado'], 403)->send();
            exit;
        }
    }

    public function cancelar($compraId, Request $request)
    {
        $this->checkAdmin($request);

        return response()->json(['message' => "Compra $compraId cancelada com sucesso"]);
    }

    public function listar(Request $request)
    {
        $usuarios = Usuario::get();
        return response()->json(['usuarios' => $usuarios]);
    }

    public function postar(UsuarioRequest $request)
    {
        $validado = $request->validated();
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
            'usuario' => $usuario
        ], 201);
    }

 
    public function buscar($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }
        return response()->json(['usuario' => $usuario]);
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
            'usuario' => $usuario
        ]);
    }


    public function restaurar(int $id)
    {
        $usuario = Usuario::withTrashed()->find($id);
        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $usuario->restore();
        return response()->json(['message' => "Usuário $id restaurado com sucesso"]);
    }

    // ---------------------------
    // Excluir usuário (somente admin)
    // ---------------------------
    public function excluir($id, Request $request)
    {
        $this->checkAdmin($request);

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $usuario->delete();

        return response()->json(['message' => "Usuário $id excluído com sucesso"]);
    }

    // ---------------------------
    // Destruir usuário permanentemente (somente admin)
    // ---------------------------
    public function destroyer(int $id, Request $request)
    {
        $this->checkAdmin($request);

        $usuario = Usuario::withTrashed()->find($id);
        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $usuario->forceDelete();

        return response()->json(['message' => "Usuário $id destruído permanentemente"]);
    }
}
