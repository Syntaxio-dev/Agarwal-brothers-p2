<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['catalogue_file', 'whatsapp_number', 'blogs_hero', 'news_hero', 'webinars_hero'];
}