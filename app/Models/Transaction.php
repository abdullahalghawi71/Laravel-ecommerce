<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'quantity',
        'user_id',
        'inventory_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
