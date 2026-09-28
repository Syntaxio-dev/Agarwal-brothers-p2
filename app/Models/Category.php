<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['brand_id', 'name', 'slug', 'description', 'image', 'sort_order'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function verticals()
    {
        return $this->belongsToMany(Vertical::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}