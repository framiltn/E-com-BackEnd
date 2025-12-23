<?php

namespace App\Jobs;

use App\Models\Payout;
use App\Models\AffiliateCommission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $type // 'seller' or 'affiliate'
    ) {}

    public function handle(): void
    {
        if ($this->type === 'affiliate') {
            $this->processAffiliatePayouts();
        } else {
            $this->processSellerPayouts();
        }
    }

    protected function processAffiliatePayouts(): void
    {
        // Get all approved commissions that haven't been paid
        $commissions = AffiliateCommission::where('status', 'approved')
            ->whereNull('payout_id')
            ->get()
            ->groupBy('affiliate_id');

        foreach ($commissions as $affiliateId => $userCommissions) {
            $totalAmount = $userCommissions->sum('commission_amount');

            if ($totalAmount < 100) { // Minimum payout threshold
                continue;
            }

            // Create payout record
            $payout = Payout::create([
                'user_id' => $affiliateId,
                'amount' => $totalAmount,
                'type' => 'affiliate',
                'status' => 'pending',
            ]);

            // Link commissions to payout
            $userCommissions->each(function ($commission) use ($payout) {
                $commission->update([
                    'payout_id' => $payout->id,
                    'status' => 'paid',
                ]);
            });

            // TODO: Process actual payment via Razorpay Payout API
            // $this->processRazorpayPayout($payout);
        }
    }

    protected function processSellerPayouts(): void
    {
        // TODO: Implement seller payout logic
        // Similar to affiliate payouts but for seller orders
    }
}
