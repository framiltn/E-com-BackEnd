<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Services\ShiprocketService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateShipmentTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $shipmentId = null
    ) {}

    public function handle(
        ShiprocketService $shiprocketService,
        NotificationService $notificationService
    ): void
    {
        $shipments = $this->shipmentId
            ? Shipment::where('id', $this->shipmentId)->get()
            : Shipment::whereIn('status', ['pending', 'picked', 'in_transit'])->get();

        foreach ($shipments as $shipment) {
            try {
                $trackingData = $shiprocketService->trackShipment($shipment->id);

                // Notify customer if status changed to delivered
                if ($shipment->status === 'delivered') {
                    $order = $shipment->sellerOrder->order;
                    $notificationService->notifyOrderDelivered(
                        $order->user_id,
                        $order->id
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Failed to update shipment tracking: ' . $e->getMessage());
            }
        }
    }
}
