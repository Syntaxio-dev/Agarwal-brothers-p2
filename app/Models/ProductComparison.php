<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** How often two products were compared by different visitors. Counts only, nothing about who compared them. */
class ProductComparison extends Model
{
    protected $fillable = ['product_id', 'other_product_id', 'times'];
}
