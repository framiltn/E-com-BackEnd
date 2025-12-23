<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * @OA\Post(
     *     path="/coupons/validate",
     *     tags={"Coupons"},
     *     summary="Validate coupon code",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","subtotal"},
     *             @OA\Property(property="code", type="string", example="SAVE10"),
     *             @OA\Property(property="subtotal", type="number", example=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon is valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="valid", type="boolean", example=true),
     *             @OA\Property(property="coupon", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Invalid coupon code"),
     *     @OA\Response(response=400, description="Coupon expired or conditions not met")
     * )
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid coupon code',
            ], 404);
        }

        // Check validity period
        if ($coupon->valid_from && now()->lt($coupon->valid_from)) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon not yet valid',
            ], 400);
        }

        if ($coupon->valid_to && now()->gt($coupon->valid_to)) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon has expired',
            ], 400);
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon usage limit reached',
            ], 400);
        }

        // Check minimum purchase
        if ($coupon->min_purchase && $request->subtotal < $coupon->min_purchase) {
            return response()->json([
                'valid' => false,
                'message' => "Minimum purchase of Rs.{$coupon->min_purchase} required",
            ], 400);
        }

        // Calculate discount
        $discount = $coupon->type === 'percentage'
            ? ($request->subtotal * $coupon->value / 100)
            : $coupon->value;

        if ($coupon->max_discount) {
            $discount = min($discount, $coupon->max_discount);
        }

        return response()->json([
            'valid' => true,
            'coupon' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount' => $discount,
            ],
        ]);
    }
}
