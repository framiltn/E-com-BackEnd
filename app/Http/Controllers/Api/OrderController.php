<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['sellerOrders.items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->with(['sellerOrders.items.product', 'transactions'])
            ->findOrFail($id);

        return response()->json(['data' => $order]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::where('user_id', auth()->id())->findOrFail($id);
        
        if ($order->order_status === 'delivered' || $order->order_status === 'cancelled') {
            return response()->json(['message' => 'Cannot update completed order'], 400);
        }

        $order->update(['order_status' => $validated['status']]);

        return response()->json(['message' => 'Order status updated', 'data' => $order]);
    }

    public function cancel($id)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);

        if ($order->order_status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be cancelled'], 400);
        }

        $order->update(['order_status' => 'cancelled']);

        return response()->json(['message' => 'Order cancelled successfully']);
    }

    public function track($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->with(['sellerOrders.shipment'])
            ->findOrFail($id);

        $tracking = $order->sellerOrders->map(function ($sellerOrder) {
            return [
                'seller_order_id' => $sellerOrder->id,
                'status' => $sellerOrder->status,
                'shipment' => $sellerOrder->shipment ? [
                    'tracking_number' => $sellerOrder->shipment->tracking_number,
                    'carrier' => $sellerOrder->shipment->carrier,
                    'status' => $sellerOrder->shipment->status,
                    'estimated_delivery' => $sellerOrder->shipment->estimated_delivery_date,
                ] : null,
            ];
        });

        return response()->json(['data' => $tracking]);
    }
}
