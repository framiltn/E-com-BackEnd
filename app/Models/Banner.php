<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['title', 'image_url', 'link', 'order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
