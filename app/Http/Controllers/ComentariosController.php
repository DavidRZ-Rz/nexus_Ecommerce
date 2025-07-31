<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comentario;
use Illuminate\Support\Facades\Validator;

class ComentariosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    if ($request->has('producto_id')) {
        $comentarios = Comentario::where('producto_id', $request->producto_id)->get();
    } else {
        $comentarios = Comentario::all();
    }

    return response()->json($comentarios);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'fecha' => 'required|date',
            'texto' => 'required|string|max:1000',
            'usuario' => 'string|max:255',
            'producto_id' => 'required|exists:productos,id',
            'calificacion' => 'required|integer|min:1|max:5'
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $comentario = Comentario::create($validator->validated());
        return response()->json($comentario, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comentario = Comentario::find($id);
        if (!$comentario){
            return response()->json(['message'=> 'comentario no encontrado'],404);
        }
        return response()->json($comentario);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $comentario = Comentario::find($id);
        if (!$comentario){
            return response()->json(['message'=> 'comentario no encontrado'],404);
        }
        $validator = Validator::make($request->all(),[
            'fecha' => 'date',
            'texto' => 'string|max:1000|    ',
            'usuario' => 'string|max:255',
            'producto_id' => 'exists:productos,id',
            'calificacion' => 'integer|min:1|max:5',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $comentario->update($validator->validated());
        return response()->json($comentario);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comentario = Comentario::find($id);
        if (!$comentario){
            return response()->json(['message'=> 'comentario no encontrado'],404);
        }
        $comentario->delete();
        return response()->json(['message' => 'comentario eliminado con exito']);
    }
}
