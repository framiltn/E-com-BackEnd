<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    protected $fillable = [
        'affiliate_id',
        'order_id',
        'product_id',
        'level',
        'commission_rate',
        'order_amount',
        'commission_amount',
        'status',
    ];

    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
