<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateOffer extends Model
{
    protected $fillable = [
        'affiliate_id',
        'type',
        'product_id',
        'seller_id',
        'coupon_id',
        'min_sales_volume',
        'achieved_volume',
        'status',
    ];

    protected $casts = [
        'min_sales_volume' => 'decimal:2',
        'achieved_volume' => 'decimal:2',
    ];

    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
