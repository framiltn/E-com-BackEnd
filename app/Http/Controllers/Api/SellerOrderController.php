<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerOrder;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = SellerOrder::where('seller_id', auth()->id())
            ->with(['order.user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = SellerOrder::where('seller_id', auth()->id())
            ->with(['order.user', 'items.product', 'shipment'])
            ->findOrFail($id);

        return response()->json(['data' => $order]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = SellerOrder::where('seller_id', auth()->id())->findOrFail($id);
        $order->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Order status updated', 'data' => $order]);
    }

    public function analytics()
    {
        $sellerId = auth()->id();

        $stats = [
            'total_orders' => SellerOrder::where('seller_id', $sellerId)->count(),
            'pending_orders' => SellerOrder::where('seller_id', $sellerId)->where('status', 'pending')->count(),
            'completed_orders' => SellerOrder::where('seller_id', $sellerId)->where('status', 'delivered')->count(),
            'total_revenue' => SellerOrder::where('seller_id', $sellerId)->sum('subtotal'),
            'monthly_revenue' => SellerOrder::where('seller_id', $sellerId)
                ->whereMonth('created_at', now()->month)
                ->sum('subtotal'),
        ];

        return response()->json(['data' => $stats]);
    }
}
