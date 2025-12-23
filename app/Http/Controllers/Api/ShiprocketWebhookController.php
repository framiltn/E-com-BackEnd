<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

class ShiprocketWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log the webhook payload
        Log::info('Shiprocket Webhook Received', $request->all());

        // Process different webhook events
        $event = $request->input('event');

        switch ($event) {
            case 'order_shipped':
                // Update order status
                $orderId = $request->input('order_id');
                // Logic to update order status
                break;

            case 'order_delivered':
                // Update order status to delivered
                break;

            case 'order_cancelled':
                // Handle cancellation
                break;

            default:
                Log::warning('Unknown Shiprocket event', ['event' => $event]);
        }

        return response()->json(['status' => 'success']);
    }
}
