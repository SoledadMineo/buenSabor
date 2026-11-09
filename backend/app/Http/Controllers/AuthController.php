<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $usuario = Usuario::where('username', $credentials['username'])->first();

        // Las cuentas anteriores pueden no tener una clave local.
        if (!$usuario || !$usuario->password || !Auth::guard('web')->attempt($credentials)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        $request->session()->regenerate();

        $usuario = Auth::guard('web')->user();

        return response()->json([
            'user' => $this->datosSesion($usuario),
        ]);
    }

    public function sesion(Request $request): JsonResponse
    {
        return response()->json($this->datosSesion($request->user()));
    }

    private function datosSesion(Usuario $usuario): array
    {
        $rol = $usuario->empleado?->rol?->value;
        if (!$rol && $usuario->cliente) {
            $rol = 'cliente';
        }

        return [
            'id' => $usuario->id,
            'username' => $usuario->username,
            'rol' => $rol,
        ];
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sesion cerrada.']);
    }
}
