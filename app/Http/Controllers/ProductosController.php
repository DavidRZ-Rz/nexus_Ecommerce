<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productos = Producto::all()->map(function ($producto) {
            $producto->imagen = url('images/' . $producto->imagen);
            return $producto;
        });

        return response()->json($productos);
    }
   

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $validator = Validator::make($request->all(),[
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'cantidad' => 'required|integer',
            'dimensiones' => 'required|string|max:255',
            'peso' => 'required|string|max:255',
            'precio' => 'required|double',
            'destacado' => 'required|boolean',
            'activo' => 'required|boolean',
            'imagen' => 'string|max:255',
            'material' => 'required|string|max:255',
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $productos = Producto::create($validator->validated());
        
        return response()->json($productos, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productos = Producto::find($id);
        if (!$productos){
            return response()->json(['message'=> 'producto no encontrado'],404);
        }
        return response()->json($productos);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $productos = Producto::find($id);
        if (!$productos){
            return response()->json(['message'=> 'producto no encontrado'],404);
        }
        $validator = Validator::make($request->all(),[
            'nombre' => 'string|max:255',
            'descripcion' => 'string|max:255',
            'cantidad' => 'integer',
            'dimensiones' => 'string|max:255',
            'peso' => 'string|max:255',
            'precio' => 'double',
            'destacado' => 'boolean',
            'activo' => 'boolean',
            'imagen' => 'string|max:255',
            'material' => 'string|max:255',
            'usuario_id' => 'integer|exists:usuarios,id',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $productos->update($validator->validated());
        return response()->json($productos);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productos = Producto::find($id);
        if (!$productos){
            return response()->json(['message'=> 'Producto no encontrado'],404);
        }
        $productos->delete();
        return response()->json(['message' => 'Producto eliminado con exito']);
    }

    public function descontarStock(Request $request, $id)
{
    $producto = Producto::find($id);
    
    if (!$producto) {
        return response()->json(['message' => 'Producto no encontrado'], 404);
    }

    $validator = Validator::make($request->all(), [
        'cantidad' => 'required|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $cantidadSolicitada = $request->input('cantidad');

    if ($producto->cantidad < $cantidadSolicitada) {
        return response()->json(['message' => 'Stock insuficiente'], 400);
    }

    $producto->cantidad -= $cantidadSolicitada;
    $producto->save();

    return response()->json([
        'message' => 'Stock actualizado',
        'producto' => $producto
    ]);
}

}
