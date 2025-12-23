<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SellerOrder;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;
use Exception;

class CheckoutService
{
    /**
     * Process checkout and create order
     */
    public function processCheckout(int $userId, array $data): Order
    {
        return DB::transaction(function () use ($userId, $data) {
            // Get cart items
            $cartItems = Cart::where('user_id', $userId)->with('product')->get();
            
            if ($cartItems->isEmpty()) {
                throw new Exception('Cart is empty');
            }

            // Validate stock availability
            $this->validateStock($cartItems);

            // Calculate totals
            $totals = $this->calculateTotals($cartItems, $data['coupon_code'] ?? null);

            // Create main order
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $totals['total'],
                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            // Split order by sellers
            $this->createSellerOrders($order, $cartItems, $totals);

            // Clear cart
            Cart::where('user_id', $userId)->delete();

            // Update coupon usage if applied
            if (!empty($data['coupon_code'])) {
                $this->updateCouponUsage($data['coupon_code']);
            }

            return $order->load('sellerOrders.items.product');
        });
    }

    /**
     * Validate stock availability
     */
    protected function validateStock($cartItems): void
    {
        foreach ($cartItems as $item) {
            $product = $item->product;
            
            if ($item->variation_id) {
                $variation = ProductVariation::find($item->variation_id);
                if (!$variation || $variation->stock < $item->quantity) {
                    throw new Exception("Insufficient stock for {$product->name} - {$variation->name}");
                }
            } else {
                if ($product->stock < $item->quantity) {
                    throw new Exception("Insufficient stock for {$product->name}");
                }
            }
        }
    }

    /**
     * Calculate order totals with coupon
     */
    protected function calculateTotals($cartItems, ?string $couponCode): array
    {
        $subtotal = 0;
        
        foreach ($cartItems as $item) {
            $price = $item->variation_id 
                ? ProductVariation::find($item->variation_id)->price 
                : $item->product->price;
            $subtotal += $price * $item->quantity;
        }

        $discount = 0;
        if ($couponCode) {
            $coupon = $this->validateCoupon($couponCode, $subtotal);
            $discount = $this->calculateDiscount($coupon, $subtotal);
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount,
        ];
    }

    /**
     * Validate coupon code
     */
    protected function validateCoupon(string $code, float $subtotal): Coupon
    {
        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            throw new Exception('Invalid coupon code');
        }

        if ($coupon->valid_from && now()->lt($coupon->valid_from)) {
            throw new Exception('Coupon not yet valid');
        }

        if ($coupon->valid_to && now()->gt($coupon->valid_to)) {
            throw new Exception('Coupon has expired');
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            throw new Exception('Coupon usage limit reached');
        }

        if ($coupon->min_purchase && $subtotal < $coupon->min_purchase) {
            throw new Exception("Minimum purchase of Rs.{$coupon->min_purchase} required");
        }

        return $coupon;
    }

    /**
     * Calculate discount amount
     */
    protected function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = $coupon->type === 'percentage'
            ? ($subtotal * $coupon->value / 100)
            : $coupon->value;

        if ($coupon->max_discount) {
            $discount = min($discount, $coupon->max_discount);
        }

        return $discount;
    }

    /**
     * Create seller orders from cart items
     */
    protected function createSellerOrders(Order $order, $cartItems, array $totals): void
    {
        // Group items by seller
        $sellerGroups = $cartItems->groupBy('product.seller_id');

        foreach ($sellerGroups as $sellerId => $items) {
            $sellerSubtotal = 0;

            // Calculate seller subtotal
            foreach ($items as $item) {
                $price = $item->variation_id 
                    ? ProductVariation::find($item->variation_id)->price 
                    : $item->product->price;
                $sellerSubtotal += $price * $item->quantity;
            }

            // Create seller order
            $sellerOrder = SellerOrder::create([
                'order_id' => $order->id,
                'seller_id' => $sellerId,
                'subtotal' => $sellerSubtotal,
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($items as $item) {
                $price = $item->variation_id 
                    ? ProductVariation::find($item->variation_id)->price 
                    : $item->product->price;

                OrderItem::create([
                    'seller_order_id' => $sellerOrder->id,
                    'product_id' => $item->product_id,
                    'variation_id' => $item->variation_id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'total' => $price * $item->quantity,
                ]);

                // Reduce stock
                if ($item->variation_id) {
                    ProductVariation::find($item->variation_id)->decrement('stock', $item->quantity);
                } else {
                    $item->product->decrement('stock', $item->quantity);
                }
            }
        }
    }

    /**
     * Update coupon usage count
     */
    protected function updateCouponUsage(string $code): void
    {
        Coupon::where('code', $code)->increment('used_count');
    }
}
