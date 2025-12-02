<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the suppliers.
     */
    public function index()
    {
        $suppliers = Supplier::all();
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:100',
            'contact_person' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:suppliers,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        // If checkbox is not checked, set is_active to false
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully!');
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'supplier_name' => 'required|string|max:100',
            'contact_person' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:suppliers,email,' . $supplier->supplier_id . ',supplier_id',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        // Optional: prevent deleting supplier if products exist
        if ($supplier->products()->count() > 0) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', 'Cannot delete supplier with associated products.');
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }
}
