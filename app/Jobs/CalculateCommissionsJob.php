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

class CalculateCommissionsJob implements ShouldQueue
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
        $order = Order::with('sellerOrders.items')->find($this->orderId);

        if (!$order) {
            return;
        }

        // Calculate commissions for all 3 levels
        $commissionService->calculateCommissions($order);

        // Get all affiliates who earned commissions
        $commissions = $order->affiliateCommissions;

        // Notify each affiliate
        foreach ($commissions as $commission) {
            $notificationService->notifyCommissionEarned(
                $commission->affiliate_id,
                $commission->commission_amount,
                $commission->level
            );
        }
    }
}
