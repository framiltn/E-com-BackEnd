<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSettings extends Model
{
    protected $fillable = [
        'seller_id',
        'store_name',
        'logo_url',
        'banner_url',
        'brand_story',
        'instagram',
        'facebook',
        'twitter',
        'website',
        'shipping_type',
        'free_shipping_threshold',
        'flat_shipping_rate',
        'tax_rate',
        'return_policy',
    ];

    protected $casts = [
        'free_shipping_threshold' => 'decimal:2',
        'flat_shipping_rate' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
