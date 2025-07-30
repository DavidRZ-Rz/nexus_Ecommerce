<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Password;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use Illuminate\Auth\Events\PasswordReset;

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


    /**
     * Enviar código de recuperación al email
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No existe un usuario con este email'
            ], 404);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); // Solo números

        DB::table('password_resets')->updateOrInsert(
            ['email' => $usuario->email],
            ['token' => $code, 'created_at' => Carbon::now()]
        );

        // Enviar email con el código
        Mail::to($usuario->email)->send(new PasswordResetMail($code));

        return response()->json([
            'success' => true,
            'message' => 'Código de verificación enviado a tu email'
        ]);
    }

    /**
     * Verificar el código de recuperación
     */
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]+$/', // Solo números
                function ($attribute, $value, $fail) {
                    if (!is_numeric($value)) {
                        $fail('El código debe contener solo dígitos numéricos.');
                    }
                }
            ]
        ]);

        // Limpieza adicional del código (por si acaso)
        $cleanCode = preg_replace('/[^0-9]/', '', $request->code);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $cleanCode) // Usamos el código limpio
            ->first();

        if (!$reset) {
            return response()->json([
                'success' => false,
                'message' => 'Código inválido o expirado'
            ], 400);
        }

        // Verifica expiración (60 minutos)
        if (Carbon::parse($reset->created_at)->addMinutes(60)->isPast()) {
            // Limpia el código expirado
            DB::table('password_resets')->where('email', $request->email)->delete();

            return response()->json([
                'success' => false,
                'message' => 'Código expirado. Por favor solicita uno nuevo.'
            ], 400);
        }

        // Genera token temporal para el reset (más seguro)
        $tempToken = hash('sha256', Str::random(60));

        // Actualiza con marca de tiempo y nuevo token
        DB::table('password_resets')
            ->where('email', $request->email)
            ->update([
                'token' => $tempToken,
                'created_at' => now() // Reinicia el tiempo para el token temporal
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Código verificado correctamente',
            'reset_token' => $tempToken,
            'expires_at' => now()->addMinutes(30)->toDateTimeString() // Informa cuando expira
        ]);
    }

    /**
     * Restablecer la contraseña
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verificar el token de reset
        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->reset_token)
            ->first();

        if (!$reset) {
            return response()->json([
                'success' => false,
                'message' => 'Token de recuperación inválido'
            ], 400);
        }

        // Verificar que no haya expirado (1 hora)
        if (Carbon::parse($reset->created_at)->addHour()->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token de recuperación expirado'
            ], 400);
        }

        // Actualizar la contraseña del usuario
        $usuario = Usuario::where('email', $request->email)->first();
        $usuario->password = Hash::make($request->password);
        $usuario->save();

        // Eliminar el registro de recuperación
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);
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
