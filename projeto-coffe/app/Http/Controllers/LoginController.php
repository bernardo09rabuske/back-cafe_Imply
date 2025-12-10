<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use App\Models\PersonalAccessToken;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $ip = $request->ip();

        if (RateLimiter::tooManyAttempts("login:{$ip}", 5)) {
            return response()->json([
                'error' => 'Muitas tentativas. Aguarde 1 minuto.'
            ], 429);
        }

        $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required']
        ], [
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'O e-mail deve ser válido',
            'senha.required' => 'A senha é obrigatória',
        ]);

        $user = Usuario::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->senha, $user->senha)) {
            RateLimiter::hit("login:{$ip}", 60);
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        RateLimiter::clear("login:{$ip}");

        // Criar token
        $tokenModel = $user->createToken('auth_token');
        $plainTextToken = $tokenModel->plainTextToken;

        // Definir expiração para 3 horas
        $tokenModel->accessToken->expires_at = Carbon::now()->addHours(3);
        $tokenModel->accessToken->save();

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'user' => $user,
            'token' => $plainTextToken,
            'expires_at' => Carbon::now()->addHours(3)->toDateTimeString()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    public function revokeAllTokens(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Todos os tokens foram revogados'
        ]);
    }

    public function checkAbility(Request $request, $ability)
    {
        if ($request->user()->tokenCan($ability)) {
            return response()->json([
                'message' => "Token possui a habilidade: $ability"
            ]);
        }

        return response()->json([
            'error' => 'Token sem permissão'
        ], 403);
    }
}
