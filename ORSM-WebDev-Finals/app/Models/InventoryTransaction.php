<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'transaction_type',
        'reference_number',
        'remarks',
        'created_at'
    ];

    public function items()
    {
        return $this->hasMany(InventoryTransactionItem::class, 'transaction_id');
    }
}
