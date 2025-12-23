<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            $this->distributeCommissions($order);
        }
    }

    protected function distributeCommissions(Order $order)
    {
        // Get the buyer
        $buyer = $order->user;
        
        // Find the affiliate who referred this buyer (Level 1)
        $referral = \App\Models\Referral::where('referee_id', $buyer->id)->first();
        
        if (!$referral) {
            return; // No referrer, no commission
        }

        $level1Affiliate = \App\Models\Affiliate::where('user_id', $referral->referrer_id)->first();
        
        if ($level1Affiliate) {
            // Level 1 Commission (6%)
            $commission1 = $order->total_amount * 0.06;
            $level1Affiliate->increment('earnings', $commission1);

            // Find Level 2
            if ($level1Affiliate->parent_id) {
                $level2Affiliate = \App\Models\Affiliate::where('user_id', $level1Affiliate->parent_id)->first();
                if ($level2Affiliate) {
                    // Level 2 Commission (4%)
                    $commission2 = $order->total_amount * 0.04;
                    $level2Affiliate->increment('earnings', $commission2);

                    // Find Level 3
                    if ($level2Affiliate->parent_id) {
                        $level3Affiliate = \App\Models\Affiliate::where('user_id', $level2Affiliate->parent_id)->first();
                        if ($level3Affiliate) {
                            // Level 3 Commission (2%)
                            $commission3 = $order->total_amount * 0.02;
                            $level3Affiliate->increment('earnings', $commission3);
                        }
                    }
                }
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
