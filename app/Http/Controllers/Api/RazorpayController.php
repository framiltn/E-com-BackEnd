<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class RazorpayController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @OA\Post(
     *     path="/payment/create-order",
     *     tags={"Payment"},
     *     summary="Create Razorpay Order",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id"},
     *             @OA\Property(property="order_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Razorpay order created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="razorpay_order_id", type="string"),
     *             @OA\Property(property="amount", type="number"),
     *             @OA\Property(property="currency", type="string"),
     *             @OA\Property(property="key", type="string")
     *         )
     *     )
     * )
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            $order = Order::findOrFail($request->order_id);
            $razorpayOrder = $this->paymentService->createOrder($order);

            return response()->json([
                'message' => 'Razorpay order created',
                'razorpay_order_id' => $razorpayOrder['order_id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'key' => config('services.razorpay.key'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create Razorpay order',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/payment/verify",
     *     tags={"Payment"},
     *     summary="Verify Razorpay Payment",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"razorpay_payment_id","razorpay_order_id","razorpay_signature"},
     *             @OA\Property(property="razorpay_payment_id", type="string"),
     *             @OA\Property(property="razorpay_order_id", type="string"),
     *             @OA\Property(property="razorpay_signature", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="transaction_id", type="string")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Payment verification failed")
     * )
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        try {
            $transactionId = $this->paymentService->verifyPayment(
                $request->razorpay_payment_id,
                $request->razorpay_order_id,
                $request->razorpay_signature
            );

            return response()->json([
                'message' => 'Payment verified successfully',
                'transaction_id' => $transactionId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment verification failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
