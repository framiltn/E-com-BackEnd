<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    protected $fillable = [
        'user_id',
        'referral_code',
        'parent_id',
        'earnings',
        'commission_scheme',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function referrals() // Direct referrals (Level 1)
    {
        return $this->hasMany(Referral::class, 'referrer_id', 'user_id');
    }
}
