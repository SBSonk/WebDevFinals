<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transaction_items', function (Blueprint $table) {
            $table->id('item_id');

            $table->foreignId('transaction_id')->nullable()->constrained('inventory_transactions', 'transaction_id')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products', 'product_id')->onDelete('restrict');
        
            $table->integer('quantity');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transaction_items');
    }
};
