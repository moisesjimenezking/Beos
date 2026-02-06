<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Currency;


class ProductPriceController extends Controller
{
    public function index($productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $prices = $product->prices()->with('currency')->get();
        return response()->json($prices);
    }

    public function store(Request $request, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        try {
            $data = $request->validate([
                'currency_id' => 'required|exists:currencies,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        $productCurrency = $product->currency;
        $priceCurrency = Currency::find($data['currency_id']);
        $basePrice = $product->price;
        $finalPrice = 0;

        if ($productCurrency->id == $priceCurrency->id) {
            $finalPrice = $basePrice;
        } else {
            if ($productCurrency->id == 1) {
                $exchangeRate = $priceCurrency->exchange_rate;
                $finalPrice = $basePrice * $exchangeRate;
            }
            elseif ($priceCurrency->id == 1) {
                $exchangeRate = $productCurrency->exchange_rate;
                $finalPrice = $basePrice / $exchangeRate;
            }
            else {
                $exchangeRate = $priceCurrency->exchange_rate;
                $finalPrice = $basePrice * $exchangeRate;
            }
        }

        $data['product_id'] = $productId;
        $data['price'] = $finalPrice;

        $price = ProductPrice::create($data);

        return response()->json($price, 201);
    }


}

