<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

/** A group clients belong to (Pharma, Dairy, Universities ...). Shown in the Our Clients section. */
class ClientCategory extends Model
{
    use LogsActivity;

    protected $fillable = ['name', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        // a category that still has clients is never deleted by accident
        static::deleting(fn (ClientCategory $category) => $category->clients()->exists() ? false : null);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
