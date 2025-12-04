<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id('inventory_id');
            
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');

            $table->integer('stock_quantity')->default(0);
            $table->integer('reorder_level')->default(10);
            $table->integer('max_stock_level');

            $table->timestamp('last_restocked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
