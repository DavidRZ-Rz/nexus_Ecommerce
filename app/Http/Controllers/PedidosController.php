<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



class PedidosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedidos = Pedido::all();
        return response()->json($pedidos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'estado' => 'string|max:255',
            'fechaPedido' => 'required|date',
            'numero' => 'required|string|max:255',
            'total' => 'required|numeric',
            'usuario_id' => 'required|integer|exists:usuarios,id'
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $pedidos = Pedido::create($validator->validated());
        
        return response()->json($pedidos, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pedidos = Pedido::find($id);
        if (!$pedidos){
            return response()->json(['message'=> 'Pedido no encontrado'],404);
        }
        return response()->json($pedidos);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pedidos = Pedido::find($id);
        if (!$pedidos){
            return response()->json(['message'=> 'pedido no encontrado'],404);
        }
        $validator = Validator::make($request->all(),[
            'estado' => 'string|max:255',
            'fechaPedido' => 'date',
            'numero' => 'string|max:255',
            'total' => 'double',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $pedidos->update($validator->validated());
        return response()->json($pedidos);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pedidos = Pedido::find($id);
        if (!$pedidos){
            return response()->json(['message'=> 'Pedido no encontrado'],404);
        }
        $pedidos->delete();
        return response()->json(['message' => 'Pedido eliminado con exito']);
    }
}
