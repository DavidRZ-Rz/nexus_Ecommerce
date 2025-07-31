<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Comentario extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'texto',
        'usuario',
        'producto_id',
        'calificacion',
    ];
    public $timestamps = false;


    //Relaciones
    public function productos()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
