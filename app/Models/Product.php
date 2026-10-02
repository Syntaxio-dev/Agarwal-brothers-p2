<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'specs',
        'image',
        'is_active',
        'is_top_pick',
        'top_pick_order',
    ];

    protected $casts = [
        'specs' => 'array',
        'is_active' => 'boolean',
        'is_top_pick' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function enquiries()
    {
        return $this->hasMany(Enquiry::class);
    }
}