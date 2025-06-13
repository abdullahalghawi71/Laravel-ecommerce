<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'description', 'unit_price', 'quantity',
        'category', 'expiry_date', 'image_path', 'sent', 'sent2', 'fast'
    ];

    public function inventories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_suppliers')
            ->withPivot('preferred_supplier', 'lead_time_days')
            ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }
}
