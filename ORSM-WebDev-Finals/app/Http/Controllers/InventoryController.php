<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load product to avoid N+1 queries
        $inventory = Inventory::with('product')->get();
        return view('inventory.index', compact('inventory'));
    }

    /**
     * Show the form for editing a specific inventory record.
     */
    public function edit($id)
    {
        $inventory = Inventory::findOrFail($id);
        $products = Product::all();
        return view('inventory.edit', compact('inventory', 'products'));
    }

    /**
     * Update a specific inventory record.
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,product_id|unique:inventory,product_id,' . $id . ',inventory_id',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'max_stock_level' => 'required|integer|min:0',
            'last_restocked' => 'required|date',
        ]);

        $inventory->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Inventory updated successfully!');
    }
}
