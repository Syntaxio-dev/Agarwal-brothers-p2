<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnquiryItem extends Model
{
    protected $fillable = ['enquiry_id', 'product_id', 'product_name', 'brand_name', 'category_name', 'quantity'];

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
