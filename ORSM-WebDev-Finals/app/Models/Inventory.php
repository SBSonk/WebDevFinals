<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';
    protected $primaryKey = 'inventory_id'; // Custom primary key
    protected $fillable = [
        'product_id',
        'stock_quantity',
        'reorder_level',
        'max_stock_level',
        'last_restocked'
    ];

    protected $casts = [
        'last_restocked' => 'datetime',
    ];

    // Inventory belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
