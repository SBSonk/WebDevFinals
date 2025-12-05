<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index()
    {
        $transactions = InventoryTransaction::with('items.product')->get();
        return view('inventory_transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new transaction.
     */
     /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        // Load products with their inventory
        $products = Product::with('inventory')->get();
        return view('inventory_transactions.create', compact('products'));
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation: Changed 'transaction_type' to 'type' to match the Blade input.
        // Also included validation for 'reference_number' and 'remarks', which are added to the Blade below.
        $validated = $request->validate([
            'type' => 'required|in:in,out', // Matches 'name="type"' in the form
            'reference_number' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // 2. Create Transaction: Map 'type' from validation back to 'transaction_type' for the Model
                $transaction = InventoryTransaction::create([
                    'transaction_type' => $validated['type'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    $transaction->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);

                    // 3. Update inventory stock
                    $product = Product::find($item['product_id']);

                    // Use firstOrCreate to guarantee an inventory record exists before attempting to update.
                    // This handles products that might not have an existing Inventory record yet.
                    $inventory = $product->inventory()->firstOrCreate(
                        ['product_id' => $product->product_id],
                        [
                            'stock_quantity'   => 0,
                            'reorder_level'    => 10,
                            'max_stock_level'  => 100,
                            'last_restocked'   => now(),
                        ]
                    );

                    if ($transaction->transaction_type === 'in') {
                        $inventory->stock_quantity += $item['quantity'];
                    } else {
                        // For 'out' transactions, the front-end should already prevent negative stock,
                        // but you could add a final server-side check here if needed.
                        $inventory->stock_quantity -= $item['quantity'];
                    }
                    $inventory->save();
                }
            });
        } catch (\Exception $e) {
            // Handle transaction failure (e.g., log error, redirect back with message)
            return redirect()->back()->withInput()->with('error', 'Transaction failed: ' . $e->getMessage());
        }


        return redirect()->route('inventory_transactions.index')
            ->with('success', 'Inventory transaction created successfully!');
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit($id)
    {
        $transaction = InventoryTransaction::with('items.product.inventory')->findOrFail($id);
        $products = Product::with('inventory')->get();
        return view('inventory_transactions.edit', compact('transaction', 'products'));
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(Request $request, $id)
    {
        $transaction = InventoryTransaction::with('items.product.inventory')->findOrFail($id);

        // 1. Validation: Changed 'transaction_type' to 'type' to match the Blade input.
        $validated = $request->validate([
            'type' => 'required|in:in,out', // Matches 'name="type"' in the form
            'reference_number' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($transaction, $validated) {

                // === 1. Rollback previous inventory changes ===
                foreach ($transaction->items as $item) {
                    // Use firstOrCreate to ensure inventory exists before rollback
                    $inventory = $item->product->inventory()->firstOrCreate(
                        ['product_id' => $item->product_id],
                        [
                            'stock_quantity'   => 0,
                            'reorder_level'    => 10,
                            'max_stock_level'  => 100,
                            'last_restocked'   => now(),
                        ]
                    );

                    if ($transaction->transaction_type === 'in') {
                        // If old type was 'in', we deduct the old quantity
                        $inventory->stock_quantity -= $item->quantity;
                    } else {
                        // If old type was 'out', we add the old quantity back
                        $inventory->stock_quantity += $item->quantity;
                    }
                    $inventory->save();
                }

                // === 2. Update Transaction Details ===
                $transaction->update([
                    'transaction_type' => $validated['type'], // Use 'type' from validation
                    'reference_number' => $validated['reference_number'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                // === 3. Delete old items and create new ones ===
                $transaction->items()->delete();

                // === 4. Apply new inventory changes ===
                foreach ($validated['items'] as $item) {
                    $transaction->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);

                    $product = Product::find($item['product_id']);

                    // Use firstOrCreate again to ensure the inventory record exists for the new item
                    $inventory = $product->inventory()->firstOrCreate(
                        ['product_id' => $product->product_id],
                        [
                            'stock_quantity'   => 0,
                            'reorder_level'    => 10,
                            'max_stock_level'  => 100,
                            'last_restocked'   => now(),
                        ]
                    );

                    if ($transaction->transaction_type === 'in') {
                        $inventory->stock_quantity += $item['quantity'];
                    } else {
                        $inventory->stock_quantity -= $item['quantity'];
                    }
                    $inventory->save();
                }
            });
        } catch (\Exception $e) {
            // Handle transaction failure (e.g., log error, redirect back with message)
            return redirect()->back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }


        return redirect()->route('inventory_transactions.index')
            ->with('success', 'Inventory transaction updated successfully!');
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy($id)
    {
        $transaction = InventoryTransaction::with('items')->findOrFail($id);

        DB::transaction(function () use ($transaction) {
            // Rollback inventory changes
            foreach ($transaction->items as $item) {
                $inventory = $item->product->inventory;
                if ($inventory) {
                    if ($transaction->transaction_type === 'in') {
                        $inventory->stock_quantity -= $item->quantity;
                    } else {
                        $inventory->stock_quantity += $item->quantity;
                    }
                    $inventory->save();
                }
            }

            $transaction->delete();
        });

        return redirect()->route('inventory_transactions.index')
            ->with('success', 'Inventory transaction deleted successfully!');
    }
}
