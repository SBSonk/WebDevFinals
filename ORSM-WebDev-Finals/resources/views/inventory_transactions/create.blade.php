@extends('layouts.app')

@section('title', 'Inventory Movement')

@section('content')
<div class="container max-w-4xl p-6 mx-auto">

    <h1 class="pb-2 mb-8 text-3xl font-extrabold text-gray-800 border-b">Inventory IN / OUT Movement</h1>

    @if (session('error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <form id="transaction-form" action="{{ route('inventory_transactions.store') }}" method="POST" class="p-6 bg-white rounded-lg shadow-xl">
        @csrf

        {{-- Movement Details --}}
        <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
            {{-- Movement Type (Input name matches Controller: 'type') --}}
            <div>
                <label for="transaction-type" class="block mb-2 text-sm font-bold text-gray-700">Movement Type <span class="text-red-500">*</span></label>
                <select id="transaction-type" name="type" class="w-full p-3 transition duration-150 ease-in-out border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="" disabled selected>Select IN or OUT</option>
                    <option value="in" {{ old('type') === 'in' ? 'selected' : '' }}>Stock IN (Add)</option>
                    <option value="out" {{ old('type') === 'out' ? 'selected' : '' }}>Stock OUT (Deduct)</option>
                </select>
                @error('type')<p class="mt-1 text-xs italic text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Reference Number --}}
            <div>
                <label for="reference_number" class="block mb-2 text-sm font-bold text-gray-700">Reference Number (Optional)</label>
                <input
                    type="text"
                    id="reference_number"
                    name="reference_number"
                    class="w-full p-3 transition duration-150 ease-in-out border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Invoice, PO, or Transfer number"
                    value="{{ old('reference_number') }}"
                >
                @error('reference_number')<p class="mt-1 text-xs italic text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Remarks --}}
        <div class="mb-8">
            <label for="remarks" class="block mb-2 text-sm font-bold text-gray-700">Remarks (Optional)</label>
            <textarea
                id="remarks"
                name="remarks"
                rows="3"
                class="w-full p-3 transition duration-150 ease-in-out border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="Reason for stock movement (e.g., received damaged goods, sale to customer X)"
            >{{ old('remarks') }}</textarea>
            @error('remarks')<p class="mt-1 text-xs italic text-red-500">{{ $message }}</p>@enderror
        </div>

        <h2 class="pt-4 mb-4 text-xl font-semibold text-gray-700 border-t">Products Affected</h2>

        <div id="product-list">
            <!-- Initial Product Row -->
            <div class="flex items-center gap-4 mb-4 product-row">
                <select name="items[0][product_id]" class="w-2/3 p-3 border rounded-lg product-select" required onchange="updateQuantityConstraints(this.closest('.product-row'))">
                    <option value="" disabled selected>Select product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->product_id }}" data-stock="{{ $product->inventory?->stock_quantity ?? 0 }}"
                            {{ (old('items.0.product_id') == $product->product_id) ? 'selected' : '' }}>
                            {{ $product->product_name }} (Stock: {{ $product->inventory?->stock_quantity ?? 0 }})
                        </option>
                    @endforeach
                </select>

                <input
                    type="number"
                    name="items[0][quantity]"
                    class="w-1/4 p-3 border rounded-lg quantity-input"
                    min="1"
                    placeholder="Qty"
                    required
                    value="{{ old('items.0.quantity') }}"
                >

                <button
                    type="button"
                    class="flex-shrink-0 p-2 text-white transition duration-150 bg-red-600 rounded-lg hover:bg-red-700 remove-row"
                    onclick="removeRow(this)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>

        <button
            type="button"
            class="px-4 py-2 mb-8 text-sm font-semibold text-white transition duration-150 bg-green-500 rounded-lg shadow-md hover:bg-green-600"
            onclick="addRow()"
        >
            + Add Product Row
        </button>
        <br>

        <button
            type="submit"
            class="w-full px-6 py-3 mt-4 text-lg font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out shadow-lg transform hover:scale-[1.01]"
        >
            Submit Inventory Movement
        </button>
    </form>
</div>

<script>
let rowIndex = 1;

// Clone of original options to regenerate dynamically
const allProducts = @json($products->map(fn($p) => [
    'id' => $p->product_id,
    'name' => $p->product_name,
    'stock' => $p->inventory?->stock_quantity ?? 0
]));

/**
 * Adds a new product row to the form.
 */
function addRow() {
    const list = document.getElementById('product-list');
    const html = `
        <div class="flex items-center gap-4 mb-4 product-row">
            <select name="items[${rowIndex}][product_id]" class="w-2/3 p-3 border rounded-lg product-select" required onchange="updateQuantityConstraints(this.closest('.product-row'))">
                <option value="" disabled selected>Select product</option>
            </select>

            <input
                type="number"
                name="items[${rowIndex}][quantity]"
                class="w-1/4 p-3 border rounded-lg quantity-input"
                min="1"
                placeholder="Qty"
                required
            >

            <button
                type="button"
                class="flex-shrink-0 p-2 text-white transition duration-150 bg-red-600 rounded-lg hover:bg-red-700 remove-row"
                onclick="removeRow(this)"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    `;
    list.insertAdjacentHTML('beforeend', html);

    // Populate options for the newly added row
    populateProductOptions(list.lastElementChild);

    // Manually trigger the initial constraint update for the new row
    updateQuantityConstraints(list.lastElementChild);

    rowIndex++;
}

/**
 * Removes a product row from the form.
 */
function removeRow(button) {
    // Prevent removing the last row
    const list = document.getElementById('product-list');
    if (list.childElementCount > 1) {
        button.closest('.product-row').remove();
    } else {
        // Optional: show a message that at least one item is required
        console.log("At least one product item is required.");
    }
}

/**
 * Populates all product select elements or a specific row's select.
 * Filters out out-of-stock items for 'out' transactions.
 */
function populateProductOptions(targetRow = null) {
    const type = document.getElementById('transaction-type').value;
    const rows = targetRow ? [targetRow] : document.querySelectorAll('.product-row');

    rows.forEach(row => {
        const select = row.querySelector('.product-select');
        const currentValue = select.value;

        select.innerHTML = '<option value="" disabled selected>Select product</option>';

        let shouldKeepSelection = false;

        allProducts.forEach(p => {
            // Filter out products with 0 stock for 'Stock OUT' transactions
            if (type === 'out' && p.stock <= 0) return;

            const option = document.createElement('option');
            option.value = p.id;
            option.dataset.stock = p.stock;
            option.text = `${p.name} (Stock: ${p.stock})`;

            // Re-select the product if it's still available/valid
            if (p.id == currentValue) {
                 option.selected = true;
                 shouldKeepSelection = true;
            }

            select.appendChild(option);
        });

        // If the transaction type changed and the previously selected item is no longer available (e.g., out-of-stock filter applied)
        if (!shouldKeepSelection && currentValue) {
            select.value = "";
            row.querySelector('.quantity-input').value = "";
        }
    });
}

/**
 * Updates the max constraint on the quantity input for a specific row.
 */
function updateQuantityConstraints(row) {
    const type = document.getElementById('transaction-type').value;
    const select = row.querySelector('.product-select');
    const qtyInput = row.querySelector('.quantity-input');

    const selectedOption = select.selectedOptions[0];
    const stock = parseInt(selectedOption?.dataset.stock || 0);

    // Clear any previous max attribute
    qtyInput.removeAttribute('max');
    qtyInput.value = qtyInput.value || 1; // Ensure a default value of at least 1

    if (type === 'out' && selectedOption) {
        // For 'Stock OUT', limit quantity to the current stock
        qtyInput.max = stock;

        // Also ensure the current value doesn't exceed the new max
        if (parseInt(qtyInput.value) > stock) {
            qtyInput.value = stock;
        }
    } else if (type === 'out' && !selectedOption) {
        // If type is 'out' but no product is selected, set max to 0
        qtyInput.max = 0;
        qtyInput.value = 0;
    }
}

/**
 * Updates quantity constraints for ALL product rows.
 * Used when the overall transaction type changes.
 */
function updateAllQuantityConstraints() {
    document.querySelectorAll('.product-row').forEach(row => {
        updateQuantityConstraints(row);
    });
}

// --- Event Listeners ---

// 1. Transaction Type Change: Re-populate options and update all constraints
document.getElementById('transaction-type').addEventListener('change', () => {
    populateProductOptions();
    updateAllQuantityConstraints();
});

// 2. Product Selection Change (Handle selections bubbling up)
document.getElementById('product-list').addEventListener('change', function(e) {
    if (e.target.classList.contains('product-select')) {
        updateQuantityConstraints(e.target.closest('.product-row'));
    }
});

// 3. Enforce Max/Min in real-time
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity-input')) {
        const max = parseInt(e.target.max);
        const min = parseInt(e.target.min || 1);
        let value = parseInt(e.target.value);

        if (value < min) {
            e.target.value = min;
        } else if (max && value > max) {
            e.target.value = max;
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initial setup for existing rows (useful for old() values on form submission failure)
    populateProductOptions();
    updateAllQuantityConstraints();
});

</script>
@endsection
