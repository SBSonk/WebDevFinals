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
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('product_name', 200);
            $table->text('description');

            $table->foreignId('category_id')->constrained('categories', 'category_id')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers', 'supplier_id')->onDelete('cascade');

            $table->decimal('unit_price', 10, 2);
            $table->decimal('cost_price', 10, 2);

            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
