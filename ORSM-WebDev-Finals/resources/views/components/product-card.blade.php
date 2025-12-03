@props(['product'])

@php 
    $inv = $product->inventory;
    $isActive = $product->is_active;
    $stock = $inv?->stock_quantity ?? 0;
@endphp

@if($isActive && $stock > 0)
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">{{ $product->product_name }}</h5>
                <p class="card-text text-muted">{{ Str::limit($product->description, 60) }}</p>
                <p class="text-primary fw-bold">${{ number_format($product->unit_price, 2) }}</p>
                <p class="text-muted small">Stock: {{ $stock }}</p>
                
                <form action="{{ route('cart.add') }}" method="post">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                    <div class="input-group mb-2">
                        <input type="number" name="qty" value="1" min="1" max="{{ $stock }}" class="form-control" />
                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@elseif(!$isActive && $stock > 0)
    <div class="col-md-4 mb-4">
        <div class="card h-100 opacity-50">
            <div class="card-body">
                <h5 class="card-title">{{ $product->product_name }}</h5>
                <p class="card-text text-muted">{{ Str::limit($product->description, 60) }}</p>
                <p class="text-warning fw-bold">${{ number_format($product->unit_price, 2) }}</p>
                <span class="badge bg-warning text-dark">Not Available</span>
            </div>
        </div>
    </div>
@elseif($isActive && $stock == 0)
    <div class="col-md-4 mb-4">
        <div class="card h-100 opacity-50">
            <div class="card-body">
                <h5 class="card-title">{{ $product->product_name }}</h5>
                <p class="card-text text-muted">{{ Str::limit($product->description, 60) }}</p>
                <p class="text-danger fw-bold">${{ number_format($product->unit_price, 2) }}</p>
                <span class="badge bg-secondary">Sold Out</span>
            </div>
        </div>
    </div>
@endif
