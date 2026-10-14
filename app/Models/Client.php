<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use LogsActivity;
    use HasFactory;

    protected $fillable = ['name', 'logo', 'client_category_id', 'city', 'since_year', 'supplied', 'testimonial', 'sort_order', 'is_active', 'is_featured'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ClientCategory::class, 'client_category_id');
    }
}
