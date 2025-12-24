<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use App\Jobs\ProcessOrderJob;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * @OA\Post(
     *     path="/checkout",
     *     tags={"Checkout"},
     *     summary="Process checkout",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"shipping_address"},
     *             @OA\Property(property="coupon_code", type="string", example="SAVE10"),
     *             @OA\Property(
     *                 property="shipping_address",
     *                 type="object",
     *                 required={"name","address","city","state","pincode","phone","email"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="city", type="string"),
     *                 @OA\Property(property="state", type="string"),
     *                 @OA\Property(property="pincode", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="email", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid input or stock issues")
     * )
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'coupon_code' => 'nullable|string',
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.pincode' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'shipping_address.email' => 'required|email',
        ]);

        try {
            $order = $this->checkoutService->processCheckout(auth()->id(), $request->all());

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order,
                'order_id' => $order->id,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Checkout failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
