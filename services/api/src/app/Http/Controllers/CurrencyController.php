<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{
    public function index()
    {
        //* verificar que los datos esten en redis
        $currencies = Cache::get('currencies.all');

        if (!$currencies) {
            sleep(4); //? Se agrega intencionalmente para simular demora en el servicio 
            $currencies = Currency::all();
            Cache::put('currencies.all', $currencies, now()->addMinutes(60));
        }

        return response()->json($currencies);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'symbol' => 'required|string|max:10',
                'exchange_rate' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        $currency = Currency::create($data);

        //* Se Borra la cache de las divisas
        Cache::forget('currencies.all');

        return response()->json($currency, 201);
    }

    public function show($id)
    {
        $currency = Currency::find($id);
        if (!$currency) {
            return response()->json(['message' => 'Divisa no encontrada'], 404);
        }
        return response()->json($currency);
    }

    public function update(Request $request, $id)
    {
        $currency = Currency::find($id);
        if (!$currency) {
            return response()->json(['message' => 'Divisa no encontrada'], 404);
        }

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'symbol' => 'required|string|max:10',
                'exchange_rate' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        $currency->update($data);

        //* Se Borra la cache de las divisas
        Cache::forget('currencies.all');

        return response()->json($currency);
    }

    public function destroy($id)
    {
        $currency = Currency::find($id);
        if (!$currency) {
            return response()->json(['message' => 'Divisa no encontrada'], 404);
        }

        $currency->delete();

        //* Se Borra la cache de las divisas
        Cache::forget('currencies.all');

        return response()->json(['message' => 'Divisa eliminada']);
    }
}
