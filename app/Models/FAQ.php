<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    protected $table = 'faqs';
    
    protected $fillable = ['question', 'answer', 'category', 'order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
