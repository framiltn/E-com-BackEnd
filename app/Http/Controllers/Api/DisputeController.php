<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index()
    {
        $disputes = Dispute::where('user_id', auth()->id())
            ->orWhere('seller_id', auth()->id())
            ->with(['order', 'user', 'seller'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($disputes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string',
            'description' => 'required|string',
        ]);

        $order = \App\Models\Order::where('user_id', auth()->id())->findOrFail($validated['order_id']);

        $dispute = Dispute::create([
            'user_id' => auth()->id(),
            'order_id' => $validated['order_id'],
            'seller_id' => $order->sellerOrders->first()->seller_id,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'status' => 'open',
        ]);

        return response()->json(['message' => 'Dispute created', 'data' => $dispute], 201);
    }

    public function show($id)
    {
        $dispute = Dispute::where('user_id', auth()->id())
            ->orWhere('seller_id', auth()->id())
            ->with(['messages.user'])
            ->findOrFail($id);

        return response()->json(['data' => $dispute]);
    }

    public function addMessage(Request $request, $id)
    {
        $validated = $request->validate(['message' => 'required|string']);

        $dispute = Dispute::where('user_id', auth()->id())
            ->orWhere('seller_id', auth()->id())
            ->findOrFail($id);

        $message = $dispute->messages()->create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        return response()->json(['message' => 'Message added', 'data' => $message], 201);
    }

    // Admin methods
    public function adminIndex()
    {
        $disputes = Dispute::with(['order', 'user', 'seller'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($disputes);
    }

    public function adminResolve(Request $request, $id)
    {
        $validated = $request->validate([
            'resolution' => 'required|string',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        $dispute = Dispute::findOrFail($id);
        $dispute->update([
            'status' => 'resolved',
            'resolution' => $validated['resolution'],
            'refund_amount' => $validated['refund_amount'] ?? null,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Dispute resolved', 'data' => $dispute]);
    }
}
