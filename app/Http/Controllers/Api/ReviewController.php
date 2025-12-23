<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * @OA\Get(
     *     path="/products/{id}/reviews",
     *     tags={"Reviews"},
     *     summary="Get reviews for a product",
     *     @OA\Parameter(name="id", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="List of reviews",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->where('status', 'approved')
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    /**
     * @OA\Post(
     *     path="/reviews",
     *     tags={"Reviews"},
     *     summary="Submit a review",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","rating"},
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(property="order_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="review", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Already reviewed or invalid input")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        // Check if user already reviewed this product
        $existing = Review::where('product_id', $request->product_id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already reviewed this product',
            ], 400);
        }

        // Check if verified purchase
        $verifiedPurchase = false;
        if ($request->order_id) {
            $verifiedPurchase = auth()->user()->orders()
                ->where('id', $request->order_id)
                ->where('payment_status', 'paid')
                ->whereHas('sellerOrders.items', function ($query) use ($request) {
                    $query->where('product_id', $request->product_id);
                })
                ->exists();
        }

        $review = Review::create([
            'product_id' => $request->product_id,
            'user_id' => auth()->id(),
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'verified_purchase' => $verifiedPurchase,
            'status' => 'pending', // Admin approval required
        ]);

        return response()->json([
            'message' => 'Review submitted successfully. It will be visible after admin approval.',
            'review' => $review,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/reviews/my",
     *     tags={"Reviews"},
     *     summary="Get user's reviews",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User's reviews",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function myReviews()
    {
        $reviews = Review::where('user_id', auth()->id())
            ->with('product:id,name')
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }
}
