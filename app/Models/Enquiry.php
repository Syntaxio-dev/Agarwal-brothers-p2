<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'name', 'email', 'phone', 'company', 'budget', 'order_location', 'message', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(EnquiryItem::class);
    }

    /** True when this came from the multi-product enquiry list. */
    public function isGroup(): bool
    {
        return $this->items()->exists();
    }
}
