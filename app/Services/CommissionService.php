<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\AffiliateCommission;
use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Calculate and create affiliate commissions for an order
     */
    public function calculateCommissions(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->sellerOrders as $sellerOrder) {
                foreach ($sellerOrder->items as $item) {
                    $this->processItemCommissions($order, $item);
                }
            }
        });
    }

    /**
     * Process commissions for a single order item
     */
    protected function processItemCommissions(Order $order, OrderItem $item): void
    {
        $product = $item->product;
        $buyer = $order->user;

        // Get buyer's affiliate record
        $buyerAffiliate = Affiliate::where('user_id', $buyer->id)->first();
        
        if (!$buyerAffiliate || !$buyerAffiliate->parent_id) {
            return; // No referrer, no commissions
        }

        // Get commission rates from product
        $rates = $this->getCommissionRates($product->commission_level);

        // Calculate commissions for 3 levels
        $this->createLevelCommissions($buyerAffiliate, $order, $item, $rates);
    }

    /**
     * Get commission rates based on product's commission level
     */
    protected function getCommissionRates(string $level): array
    {
        return match($level) {
            '6-4-2' => [6.00, 4.00, 2.00],
            '9-6-3' => [9.00, 6.00, 3.00],
            '12-8-4' => [12.00, 8.00, 4.00],
            default => [6.00, 4.00, 2.00],
        };
    }

    /**
     * Create commission records for up to 3 levels
     */
    protected function createLevelCommissions(Affiliate $buyerAffiliate, Order $order, OrderItem $item, array $rates): void
    {
        $currentAffiliate = $buyerAffiliate;
        $orderAmount = $item->total;

        for ($level = 1; $level <= 3; $level++) {
            if (!$currentAffiliate->parent_id) {
                break; // No more parents in chain
            }

            $parentAffiliate = Affiliate::where('user_id', $currentAffiliate->parent_id)->first();
            
            if (!$parentAffiliate) {
                break;
            }

            $commissionRate = $rates[$level - 1];
            $commissionAmount = ($orderAmount * $commissionRate) / 100;

            // Create commission record
            AffiliateCommission::create([
                'affiliate_id' => $parentAffiliate->user_id,
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'level' => $level,
                'commission_rate' => $commissionRate,
                'order_amount' => $orderAmount,
                'commission_amount' => $commissionAmount,
                'status' => 'pending',
            ]);

            // Update affiliate earnings
            $parentAffiliate->increment('earnings', $commissionAmount);

            // Move to next level
            $currentAffiliate = $parentAffiliate;
        }
    }

    /**
     * Auto-enroll user as affiliate after first order
     */
    public function autoEnrollAffiliate(User $user, ?string $referralCode = null): void
    {
        // Check if already enrolled
        if (Affiliate::where('user_id', $user->id)->exists()) {
            return;
        }

        $parentId = null;

        // Find parent if referral code provided
        if ($referralCode) {
            $parentAffiliate = Affiliate::where('referral_code', $referralCode)->first();
            if ($parentAffiliate) {
                $parentId = $parentAffiliate->user_id;
            }
        }

        // Create affiliate record
        Affiliate::create([
            'user_id' => $user->id,
            'referral_code' => $this->generateReferralCode($user),
            'parent_id' => $parentId,
            'earnings' => 0,
            'commission_scheme' => 'standard',
        ]);
    }

    /**
     * Generate unique referral code
     */
    protected function generateReferralCode(User $user): string
    {
        $code = strtoupper(substr($user->name, 0, 3) . rand(1000, 9999));
        
        // Ensure uniqueness
        while (Affiliate::where('referral_code', $code)->exists()) {
            $code = strtoupper(substr($user->name, 0, 3) . rand(1000, 9999));
        }

        return $code;
    }

    /**
     * Get affiliate tree (3 levels deep)
     */
    public function getAffiliateTree(int $userId): array
    {
        $affiliate = Affiliate::where('user_id', $userId)->first();
        
        if (!$affiliate) {
            return [];
        }

        return $this->buildTree($affiliate, 1);
    }

    /**
     * Recursively build affiliate tree
     */
    protected function buildTree(Affiliate $affiliate, int $currentLevel, int $maxLevel = 3): array
    {
        $node = [
            'id' => $affiliate->user_id,
            'name' => $affiliate->user->name,
            'referral_code' => $affiliate->referral_code,
            'earnings' => $affiliate->earnings,
            'level' => $currentLevel,
            'children' => [],
        ];

        if ($currentLevel < $maxLevel) {
            $children = Affiliate::where('parent_id', $affiliate->user_id)->get();
            
            foreach ($children as $child) {
                $node['children'][] = $this->buildTree($child, $currentLevel + 1, $maxLevel);
            }
        }

        return $node;
    }
}
