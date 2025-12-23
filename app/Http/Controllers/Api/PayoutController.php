<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Payout;

class PayoutController extends Controller
{
    /**
     * @OA\Get(
     *     path="/payouts",
     *     tags={"Payouts"},
     *     summary="Get payout history",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of payouts",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="amount", type="number"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="type", type="string")
     *         ))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $payouts = Payout::where('user_id', $request->user()->id)->get();
        return response()->json($payouts);
    }

    /**
     * @OA\Post(
     *     path="/payouts",
     *     tags={"Payouts"},
     *     summary="Request a payout",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount","type"},
     *             @OA\Property(property="amount", type="number", example=100.00),
     *             @OA\Property(property="type", type="string", enum={"seller","affiliate"}, example="affiliate")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Payout requested")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'type' => 'required|in:seller,affiliate',
        ]);

        $payout = Payout::create([
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'type' => $request->type,
            'status' => 'pending',
        ]);

        return response()->json($payout, 201);
    }
}
