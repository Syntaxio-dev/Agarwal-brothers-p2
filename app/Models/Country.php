<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use LogsActivity;
    protected $fillable = ['name', 'latitude', 'longitude'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }
}
