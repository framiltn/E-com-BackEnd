<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\SellerOrder;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    public function dashboard()
    {
        $sellerId = Auth::id();

        $stats = [
            'total_products' => Product::where('seller_id', $sellerId)->count(),
            'total_orders' => SellerOrder::where('seller_id', $sellerId)->count(),
            'total_revenue' => SellerOrder::where('seller_id', $sellerId)
                                ->where('status', 'delivered')
                                ->sum('subtotal'),
            'pending_orders' => SellerOrder::where('seller_id', $sellerId)
                                ->where('status', 'pending')
                                ->count(),
        ];

        return response()->json(['data' => $stats]);
    }
}
