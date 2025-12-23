<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AffiliateOffer;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateOfferController extends Controller
{
    public function myOffers()
    {
        $offers = AffiliateOffer::where('affiliate_id', auth()->id())
            ->with(['product', 'seller', 'coupon'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $offers]);
    }

    public function checkEligibility()
    {
        $affiliateId = auth()->id();
        $currentMonth = now()->startOfMonth();

        // Check product-level offers
        $productSales = \DB::table('affiliate_commissions')
            ->join('orders', 'affiliate_commissions.order_id', '=', 'orders.id')
            ->join('seller_orders', 'orders.id', '=', 'seller_orders.order_id')
            ->join('order_items', 'seller_orders.id', '=', 'order_items.seller_order_id')
            ->where('affiliate_commissions.affiliate_id', $affiliateId)
            ->where('orders.created_at', '>=', $currentMonth)
            ->select('order_items.product_id', \DB::raw('SUM(order_items.quantity * order_items.price) as total_sales'))
            ->groupBy('order_items.product_id')
            ->get();

        // Check brand-level offers
        $brandSales = \DB::table('affiliate_commissions')
            ->join('orders', 'affiliate_commissions.order_id', '=', 'orders.id')
            ->join('seller_orders', 'orders.id', '=', 'seller_orders.order_id')
            ->where('affiliate_commissions.affiliate_id', $affiliateId)
            ->where('orders.created_at', '>=', $currentMonth)
            ->select('seller_orders.seller_id', \DB::raw('SUM(seller_orders.total_amount) as total_sales'))
            ->groupBy('seller_orders.seller_id')
            ->get();

        return response()->json([
            'product_sales' => $productSales,
            'brand_sales' => $brandSales,
        ]);
    }

    public function shareCoupon(Request $request)
    {
        $validated = $request->validate([
            'coupon_id' => 'required|exists:coupons,id',
            'affiliate_ids' => 'required|array',
            'affiliate_ids.*' => 'exists:users,id',
        ]);

        $coupon = Coupon::findOrFail($validated['coupon_id']);

        // Verify ownership or eligibility
        $offer = AffiliateOffer::where('affiliate_id', auth()->id())
            ->where('coupon_id', $coupon->id)
            ->firstOrFail();

        // Share with downline affiliates
        foreach ($validated['affiliate_ids'] as $affiliateId) {
            \DB::table('shared_coupons')->insert([
                'coupon_id' => $coupon->id,
                'shared_by' => auth()->id(),
                'shared_to' => $affiliateId,
                'created_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Coupon shared successfully']);
    }

    // Admin/Seller: Create offer
    public function createOffer(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:product,brand',
            'product_id' => 'required_if:type,product|exists:products,id',
            'seller_id' => 'required_if:type,brand|exists:users,id',
            'min_sales_volume' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_days' => 'required|integer|min:1',
        ]);

        // Auto-generate coupons for eligible affiliates
        $eligibleAffiliates = $this->getEligibleAffiliates($validated);

        foreach ($eligibleAffiliates as $affiliateId) {
            $coupon = Coupon::create([
                'code' => 'AFF-' . strtoupper(Str::random(8)),
                'type' => $validated['discount_type'],
                'value' => $validated['discount_value'],
                'valid_from' => now(),
                'valid_until' => now()->addDays($validated['valid_days']),
                'usage_limit' => 1,
                'user_id' => $affiliateId,
            ]);

            AffiliateOffer::create([
                'affiliate_id' => $affiliateId,
                'type' => $validated['type'],
                'product_id' => $validated['product_id'] ?? null,
                'seller_id' => $validated['seller_id'] ?? null,
                'coupon_id' => $coupon->id,
                'min_sales_volume' => $validated['min_sales_volume'],
            ]);
        }

        return response()->json(['message' => 'Offers created for eligible affiliates']);
    }

    private function getEligibleAffiliates($criteria)
    {
        // Logic to find affiliates meeting sales volume criteria
        return []; // Placeholder
    }
}
