<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $primaryKey = 'supplier_id'; // Custom primary key
    protected $fillable = [
        'supplier_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'is_active'
    ];

    // A supplier has many products
    public function products()
    {
        return $this->hasMany(Product::class, 'supplier_id', 'supplier_id');
    }
}
