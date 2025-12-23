<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send notification to user
     */
    public function send(int $userId, string $type, string $title, string $message, array $data = []): void
    {
        // Create in-app notification
        Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        // Send email notification
        $this->sendEmail($userId, $title, $message);
    }

    /**
     * Send email notification
     */
    protected function sendEmail(int $userId, string $title, string $message): void
    {
        $user = User::find($userId);
        
        if (!$user) {
            return;
        }

        // TODO: Implement email sending
        // Mail::to($user->email)->send(new GenericNotification($title, $message));
    }

    /**
     * Notify order placed
     */
    public function notifyOrderPlaced(int $userId, int $orderId): void
    {
        $this->send(
            $userId,
            'order_placed',
            'Order Placed Successfully',
            "Your order #{$orderId} has been placed successfully.",
            ['order_id' => $orderId]
        );
    }

    /**
     * Notify order shipped
     */
    public function notifyOrderShipped(int $userId, int $orderId, string $trackingNumber): void
    {
        $this->send(
            $userId,
            'order_shipped',
            'Order Shipped',
            "Your order #{$orderId} has been shipped. Tracking number: {$trackingNumber}",
            ['order_id' => $orderId, 'tracking_number' => $trackingNumber]
        );
    }

    /**
     * Notify order delivered
     */
    public function notifyOrderDelivered(int $userId, int $orderId): void
    {
        $this->send(
            $userId,
            'order_delivered',
            'Order Delivered',
            "Your order #{$orderId} has been delivered successfully.",
            ['order_id' => $orderId]
        );
    }

    /**
     * Notify refund processed
     */
    public function notifyRefundProcessed(int $userId, int $orderId, float $amount): void
    {
        $this->send(
            $userId,
            'refund_processed',
            'Refund Processed',
            "Your refund of Rs.{$amount} for order #{$orderId} has been processed.",
            ['order_id' => $orderId, 'amount' => $amount]
        );
    }

    /**
     * Notify commission earned
     */
    public function notifyCommissionEarned(int $affiliateId, float $amount, int $level): void
    {
        $this->send(
            $affiliateId,
            'commission_earned',
            'Commission Earned',
            "You earned Rs.{$amount} as Level {$level} commission.",
            ['amount' => $amount, 'level' => $level]
        );
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): void
    {
        Notification::where('id', $notificationId)->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications for user
     */
    public function getUnread(int $userId)
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
