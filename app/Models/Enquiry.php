<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'name', 'email', 'phone', 'budget', 'order_location', 'message', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}