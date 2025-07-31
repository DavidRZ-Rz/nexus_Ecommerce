<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\ComentariosController;
use App\Http\Controllers\PaymentController;

Route::post('login', [UsuariosController::class, 'login']);
Route::post('register', [UsuariosController::class, 'registrar']);
Route::post('/password/email', [UsuariosController::class, 'forgotPassword']);
Route::post('/password/verify', [UsuariosController::class, 'verifyResetCode']);
Route::post('/password/reset', [UsuariosController::class, 'resetPassword']);

Route::get('/reset-password/{token}', function ($token) {
    return response()->json([
        'message' => 'Frontend debe manejar esta ruta',
        'token' => $token,
        'email' => request('email')
    ]);
})->name('password.reset');

Route::middleware(['jwt.auth'])->group(function () {

    // ✅ Esta es la nueva ruta para el perfil del usuario autenticado
    Route::get('me', function (Request $request) {
        return response()->json($request->user());
    });
    Route::post('cambiar-password', [UsuariosController::class, 'cambiarPassword']);

    Route::put('editarPerfil', [UsuariosController::class, 'editarPerfil']);
    Route::get('user', [UsuariosController::class, 'getUserTipo']); // Cambiado de getUserRole a getUserTipo
    Route::post('logout', [UsuariosController::class, 'logout']);
    Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);

    // Rutas para user y admin
    Route::middleware(['tipo:USER,ADMIN'])->group(function () { // Cambiado de role a tipo

        Route::controller(ProductosController::class)->group(function () {
            Route::get('productos', 'index');
            Route::get('producto/{id}', 'show');
             Route::patch('producto/{id}/descontar-stock', 'descontarStock');
        });

        Route::controller(PedidosController::class)->group(function () {
            Route::get('pedidos', 'index');
            Route::post('pedido', 'store');
            Route::get('pedido/{id}', 'show');
        });

        Route::controller(ComentariosController::class)->group(function () {
            Route::post('comentario', 'store');
            Route::get('comentarios', 'index');
            Route::get('comentarios/{producto_id}', 'getByProducto');
        });
    });

    // Rutas solo para admin
    Route::middleware(['tipo:ADMIN'])->group(function () { // Cambiado de role a tipo

        Route::controller(ProductosController::class)->group(function () {
            Route::post('crearProducto', 'store');
            Route::put('editarProducto/{id}', 'update');
            Route::delete('eliminarProducto/{id}', 'destroy');
        });

        Route::controller(UsuariosController::class)->group(function () {
            Route::get('usuarios', 'index');
            Route::get('usuario/{id}', 'show');
            Route::put('editarUsuario/{id}', 'update');
            Route::delete('eliminarUsuario/{id}', 'destroy');
        });
    });
});