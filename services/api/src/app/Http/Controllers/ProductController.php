<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class ProductController extends Controller
{
    
    public function index()
    {
        $products = Cache::get('products.all');

        if (!$products) {
            //? Se agrega intencionalmente para simular demora en el servicio 
            sleep(2);

            $products = Product::with('currency')->get();
            Cache::put('products.all', $products, now()->addMinutes(60));
        }

        return response()->json($products);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'currency_id' => 'required|exists:currencies,id',
                'tax_cost' => 'required|numeric|min:0',
                'manufacturing_cost' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        $product = Product::create($data);

        // Limpiar cache
        Cache::forget('products.all');

        return response()->json($product, 201);
    }

    public function show($id)
    {
        $product = Product::with('currency')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        try {
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0',
                'currency_id' => 'sometimes|exists:currencies,id',
                'tax_cost' => 'sometimes|numeric|min:0',
                'manufacturing_cost' => 'sometimes|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        $product->update($data);

        Cache::forget('products.all');

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $product->delete();

        Cache::forget('products.all');

        return response()->json(['message' => 'Producto eliminado']);
    }
}
