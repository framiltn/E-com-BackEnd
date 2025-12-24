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
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantityToAdd = $request->quantity ?? 1;

        // Check if product is approved
        if ($product->status !== 'approved') {
            return response()->json(['error' => 'Product is not available'], 400);
        }

        // Minimum price check
        if ($product->price < 1200) {
            return response()->json(['error' => 'Product price is below minimum allowed (₹1200).'], 400);
        }

        // Stock and Variation check
        if ($request->variation_id) {
            $variation = \App\Models\ProductVariation::where('product_id', $product->id)
                ->findOrFail($request->variation_id);
            
            $existingQty = Cart::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->where('variation_id', $request->variation_id)
                ->value('quantity') ?? 0;

            if ($variation->stock < ($existingQty + $quantityToAdd)) {
                return response()->json(['error' => 'Not enough stock available for this variation'], 400);
            }
        } else {
            $existingQty = Cart::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->whereNull('variation_id')
                ->value('quantity') ?? 0;

            if ($product->stock < ($existingQty + $quantityToAdd)) {
                return response()->json(['error' => 'Not enough stock available'], 400);
            }
        }

        // Add or update cart
        $cartItem = Cart::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->where('variation_id', $request->variation_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantityToAdd;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'variation_id' => $request->variation_id,
                'quantity' => $quantityToAdd
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
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->where('variation_id', $request->variation_id)
            ->firstOrFail();

        // Stock validation for update
        if ($request->variation_id) {
            $variation = \App\Models\ProductVariation::findOrFail($request->variation_id);
            if ($variation->stock < $request->quantity) {
                return response()->json(['error' => 'Not enough stock available for this variation'], 400);
            }
        } else {
            $product = Product::findOrFail($request->product_id);
            if ($product->stock < $request->quantity) {
                return response()->json(['error' => 'Not enough stock available'], 400);
            }
        }

        $cart->update([
            'quantity' => $request->quantity
        ]);

        return response()->json(['message' => 'Quantity updated']);
    }

    // 👉 Remove item
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id'
        ]);

        Cart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->where('variation_id', $request->variation_id)
            ->delete();

        return response()->json(['message' => 'Item removed']);
    }
}
