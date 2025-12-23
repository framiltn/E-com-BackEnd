<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @OA\Get(
     *     path="/refunds",
     *     tags={"Refunds"},
     *     summary="Get user's refund requests",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of refunds",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function index()
    {
        $refunds = Refund::where('user_id', auth()->id())
            ->with('order')
            ->latest()
            ->paginate(10);

        return response()->json($refunds);
    }

    /**
     * @OA\Post(
     *     path="/refunds",
     *     tags={"Refunds"},
     *     summary="Request a refund",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id","reason"},
     *             @OA\Property(property="order_id", type="integer"),
     *             @OA\Property(property="reason", type="string", enum={"customer_not_available","defective","wrong_item","other"}),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Refund request submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="refund", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=400, description="Invalid request or already exists")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|in:customer_not_available,defective,wrong_item,other',
            'description' => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Verify order belongs to user
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if order is paid
        if ($order->payment_status !== 'paid') {
            return response()->json([
                'message' => 'Order payment not completed',
            ], 400);
        }

        // Check if refund already exists
        if ($order->refunds()->where('status', '!=', 'rejected')->exists()) {
            return response()->json([
                'message' => 'Refund request already exists for this order',
            ], 400);
        }

        // Calculate refund amount based on reason
        $refundAmount = $order->total_amount;
        
        if ($request->reason === 'customer_not_available') {
            // Deduct shipping costs (both ways)
            // TODO: Get actual shipping cost from order
            $shippingCost = 100; // Placeholder
            $refundAmount = $order->total_amount - ($shippingCost * 2);
        }

        $refund = Refund::create([
            'order_id' => $request->order_id,
            'user_id' => auth()->id(),
            'amount' => $refundAmount,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Refund request submitted successfully',
            'refund' => $refund,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/refunds/{id}",
     *     tags={"Refunds"},
     *     summary="Get refund details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", description="Refund ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Refund details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Refund not found")
     * )
     */
    public function show($id)
    {
        $refund = Refund::where('user_id', auth()->id())
            ->with('order')
            ->findOrFail($id);

        return response()->json($refund);
    }
}
