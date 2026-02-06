<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Currency;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'description', 
        'price', 
        'currency_id', 
        'tax_cost', 
        'manufacturing_cost'
    ];

    //? Relación con divisas
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }
}
