<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_email',
        'risk_score'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_suppliers')
            ->withPivot('preferred_supplier', 'lead_time_days')
            ->withTimestamps();
    }
}
