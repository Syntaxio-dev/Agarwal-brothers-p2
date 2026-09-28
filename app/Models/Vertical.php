<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vertical extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'image', 'sort_order', 'is_active'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}