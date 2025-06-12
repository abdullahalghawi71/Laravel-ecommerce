<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    /** @use HasFactory<\Database\Factories\IssueFactory> */
    use HasFactory;

    protected $fillable = ['product_id', 'description', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
