<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Password;
use App\Notifications\ResetPasswordNotification;

class UsuariosController extends Controller
{
    public function registrar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'tipo' => 'required|string|max:255', // Cambiado de role a tipo
            'username' => 'required|string|max:255',
            'imagenUrl' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tipo' => $request->tipo, // Cambiado de role a tipo
            'username' => $request->username,
            'imagenUrl' => $request->imagenUrl,
        ]);

        try {
            $token = JWTAuth::fromUser($usuario);
            return response()->json([
                'success' => true,
                'usuario' => $usuario,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el token JWT',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada con éxito',
        ]);
    }

    public function tipo(Request $request) // Cambiado de role() a tipo()
    {
        return response()->json([
            'success' => true,
            'tipo' => $request->user()->tipo, // Cambiado de role a tipo
        ]);
    }

    public function getUserTipo(Request $request) // Cambiado de getUserRole a getUserTipo
    {
        try {
            $usuario = JWTAuth::parseToken()->authenticate();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'usuario_id' => $usuario->id,
                'tipo' => $usuario->tipo, // Cambiado de role a tipo
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido o expirado',
            ], 401);
        }
    }


     public function forgotPassword(Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'message' => $status === Password::RESET_LINK_SENT
                ? 'Email de recuperación enviado'
                : 'No se pudo enviar el email'
        ], $status === Password::RESET_LINK_SENT ? 200 : 400);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();

                $user->tokens()->delete(); // opcional: cerrar sesiones
            }
        );

        return response()->json([
            'message' => $status === Password::PASSWORD_RESET
                ? 'Contraseña actualizada'
                : 'Token inválido o expirado'
        ], $status === Password::PASSWORD_RESET ? 200 : 400);
    }

    /**
     * Editar el perfil del usuario autenticado.
     */
    public function editarPerfil(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'direccion' => 'sometimes|string|max:255',
            'telefono' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,' .
            $user->id,
            'tipo' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

        // Hashear la contraseña si está presente
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json($user);
    }

     // Retorna el usuario autenticado
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    }
}
