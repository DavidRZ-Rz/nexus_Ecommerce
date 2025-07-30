<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'password',
        'tipo', // Cambiado de 'role' a 'tipo'
        'username',
        'imagenUrl',
        'activo',
    ];

     protected $attributes = [
        'activo' => true, // <-- Valor por defecto
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'tipo' => $this->tipo, // Cambiado de 'role' a 'tipo'
        ];
    }

    public function hasRole($tipo) // Cambiado el nombre del parámetro
    {
        return $this->tipo === $tipo; // Cambiado de 'role' a 'tipo'
    }

    public $timestamps = false;

    // Relaciones
    public function productos()
    {
        return $this->hasMany(Producto::class, 'usuario_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }
}