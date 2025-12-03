<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'customer_id',
        'order_date',
        'order_status',
        'total_amount',
        'payment_status',
        'payment_method',
        'shipping_address',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }
}
