<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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

    public function transactionForm()
    {
        $products = Product::with('inventory')->get();
        return view('inventory.transaction', compact('products'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,product_id',
            'products.*.qty' => 'required|numeric|min:1',
        ]);

        foreach ($request->products as $item) {
            $inventory = Inventory::where('product_id', $item['id'])->first();

            if ($request->type === 'in') {
                $inventory->stock_quantity += $item['qty'];
            } else {
                // prevent negative stock
                $inventory->stock_quantity = max(0, $inventory->stock_quantity - $item['qty']);
            }

            $inventory->last_restocked = now();
            $inventory->save();
        }

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory updated successfully!');
    }

}
