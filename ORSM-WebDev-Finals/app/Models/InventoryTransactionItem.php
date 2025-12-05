<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransactionItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'item_id';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
    ];

    public function transaction()
    {
        return $this->belongsTo(InventoryTransaction::class, 'transaction_id');
    }

    public function product()
    {
        // Include soft-deleted products so historical transactions still show names
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }
}
