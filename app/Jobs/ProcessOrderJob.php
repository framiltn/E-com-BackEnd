<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\CommissionService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId
    ) {}

    public function handle(
        CommissionService $commissionService,
        NotificationService $notificationService
    ): void
    {
        $order = Order::find($this->orderId);

        if (!$order) {
            return;
        }

        // Calculate affiliate commissions
        $commissionService->calculateCommissions($order);

        // Auto-enroll user as affiliate if first order
        if ($order->user->orders()->count() === 1) {
            $commissionService->autoEnrollAffiliate($order->user);
        }

        // Send notification to customer
        $notificationService->notifyOrderPlaced($order->user_id, $order->id);

        // Notify sellers
        foreach ($order->sellerOrders as $sellerOrder) {
            $notificationService->send(
                $sellerOrder->seller_id,
                'new_order',
                'New Order Received',
                "You have a new order #{$order->id}",
                ['order_id' => $order->id, 'seller_order_id' => $sellerOrder->id]
            );
        }
    }
}
