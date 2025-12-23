<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerApplication extends Model
{
    protected $fillable = [
        'user_id',
        'store_name',
        'business_name',
        'business_type',
        'pan_number',
        'brand_logo',
        'gst_number',
        'mobile',
        'address',
        'about_store',
        'instagram',
        'facebook',
        'website',
        'status',
        'reviewed_at',
        'admin_notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::updated(function ($application) {
            if ($application->isDirty('status') && $application->status === 'approved') {
                if ($application->user) {
                    $application->user->assignRole('seller');
                }
            }
        });
    }
}
