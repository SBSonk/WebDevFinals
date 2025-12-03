@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Store</h1>
        <a href="{{ route('cart.index') }}" class="btn btn-outline-primary">View Cart</a>
    </div>
    
    <div class="row">
        @forelse($products as $product)
            <x-product-card :product="$product" />
        @empty
            <div class="col-12">
                <p class="alert alert-info">No products available.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
