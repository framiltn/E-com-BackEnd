<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * @OA\Post(
     *     path="/cart/add",
     *     tags={"Cart"},
     *     summary="Add item to cart",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Item added to cart")
     * )
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check if product is approved
        if ($product->status !== 'approved') {
            return response()->json(['error' => 'Product is not available'], 400);
        }

        // Minimum price check
        if ($product->price < 1200) {
            return response()->json(['error' => 'Product price is below minimum allowed (₹1200).'], 400);
        }

        // Stock check
        if ($product->stock < 1) {
            return response()->json(['error' => 'Product is out of stock'], 400);
        }

        // Add or update cart
        $cartItem = Cart::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity ?? 1;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'quantity' => $request->quantity ?? 1
            ]);
        }

        return response()->json(['message' => 'Added to cart', 'cart' => $cartItem]);
    }

    // 👉 View cart
    public function view()
    {
        $cart = Cart::with(['product.images', 'product.seller'])
            ->where('user_id', auth()->id())
            ->get();

        $items = $cart->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product' => [
                    'name' => $item->product->name,
                    'images' => $item->product->getAllImages(),
                ],
                'price' => $item->product->price,
                'quantity' => $item->quantity,
                'total' => $item->quantity * $item->product->price,
            ];
        });

        $total = $items->sum('total');

        return response()->json([
            'items' => $items,
            'total' => $total,
            'count' => $items->count()
        ]);
    }

    // 👉 Update quantity
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->firstOrFail();

        $cart->update([
            'quantity' => $request->quantity
        ]);

        return response()->json(['message' => 'Quantity updated']);
    }

    // 👉 Remove item
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        Cart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->delete();

        return response()->json(['message' => 'Item removed']);
    }
}
