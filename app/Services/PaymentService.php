<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SellerOrder;
use App\Models\Transaction;
use App\Models\Invoice;
use Razorpay\Api\Api;
use Exception;

class PaymentService
{
    protected Api $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    /**
     * Create Razorpay order with split payment
     */
    public function createOrder(Order $order): array
    {
        try {
            $razorpayOrder = $this->razorpay->order->create([
                'amount' => $order->total_amount * 100, // Convert to paise
                'currency' => 'INR',
                'receipt' => 'order_' . $order->id,
                'notes' => [
                    'order_id' => $order->id,
                ],
            ]);

            // Update order with Razorpay order ID
            $order->update([
                'payment_id' => $razorpayOrder->id,
            ]);

            return [
                'order_id' => $razorpayOrder->id,
                'amount' => $razorpayOrder->amount,
                'currency' => $razorpayOrder->currency,
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to create Razorpay order: ' . $e->getMessage());
        }
    }

    /**
     * Verify payment and process split transfers
     */
    public function verifyPayment(array $data): bool
    {
        try {
            // Verify signature
            $attributes = [
                'razorpay_order_id' => $data['razorpay_order_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
            ];

            $this->razorpay->utility->verifyPaymentSignature($attributes);

            // Find order
            $order = Order::where('payment_id', $data['razorpay_order_id'])->firstOrFail();

            // Update order status
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
            ]);

            // Create transaction record
            Transaction::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'type' => 'payment',
                'amount' => $order->total_amount,
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_order_id' => $data['razorpay_order_id'],
                'status' => 'completed',
            ]);

            // Process split payments to sellers
            $this->processSplitPayments($order, $data['razorpay_payment_id']);

            // Generate invoices
            $this->generateInvoices($order);

            return true;
        } catch (Exception $e) {
            throw new Exception('Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Process split payments to sellers
     */
    protected function processSplitPayments(Order $order, string $paymentId): void
    {
        foreach ($order->sellerOrders as $sellerOrder) {
            try {
                // Create transfer to seller
                $transfer = $this->razorpay->payment->fetch($paymentId)->transfer([
                    'transfers' => [
                        [
                            'account' => $this->getSellerAccountId($sellerOrder->seller_id),
                            'amount' => $sellerOrder->subtotal * 100, // Convert to paise
                            'currency' => 'INR',
                            'notes' => [
                                'seller_order_id' => $sellerOrder->id,
                                'seller_id' => $sellerOrder->seller_id,
                            ],
                        ],
                    ],
                ]);

                // Log transfer
                Transaction::create([
                    'order_id' => $order->id,
                    'user_id' => $sellerOrder->seller_id,
                    'type' => 'payout',
                    'amount' => $sellerOrder->subtotal,
                    'razorpay_payment_id' => $paymentId,
                    'status' => 'completed',
                    'metadata' => json_encode($transfer),
                ]);
            } catch (Exception $e) {
                // Log error but don't fail the entire payment
                \Log::error('Split payment failed for seller ' . $sellerOrder->seller_id . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Get seller's Razorpay account ID
     * In production, this should be stored in seller's profile
     */
    protected function getSellerAccountId(int $sellerId): string
    {
        // TODO: Implement seller account ID retrieval from database
        // For now, return a placeholder
        return config('services.razorpay.seller_account_prefix') . $sellerId;
    }

    /**
     * Generate invoices for each seller order
     */
    protected function generateInvoices(Order $order): void
    {
        foreach ($order->sellerOrders as $sellerOrder) {
            $invoiceNumber = $this->generateInvoiceNumber($sellerOrder);

            Invoice::create([
                'seller_order_id' => $sellerOrder->id,
                'seller_id' => $sellerOrder->seller_id,
                'invoice_number' => $invoiceNumber,
                'subtotal' => $sellerOrder->subtotal,
                'tax' => 0, // TODO: Calculate tax
                'shipping' => 0, // TODO: Calculate shipping
                'total' => $sellerOrder->subtotal,
            ]);

            // TODO: Generate PDF invoice
        }
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(SellerOrder $sellerOrder): string
    {
        return 'INV-' . date('Ymd') . '-' . str_pad($sellerOrder->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Process refund
     */
    public function processRefund(int $orderId, float $amount, string $reason): array
    {
        try {
            $order = Order::findOrFail($orderId);

            if ($order->payment_status !== 'paid') {
                throw new Exception('Order payment not completed');
            }

            // Get payment ID from transaction
            $transaction = Transaction::where('order_id', $orderId)
                ->where('type', 'payment')
                ->where('status', 'completed')
                ->firstOrFail();

            // Create refund
            $refund = $this->razorpay->payment
                ->fetch($transaction->razorpay_payment_id)
                ->refund(['amount' => $amount * 100]); // Convert to paise

            // Create refund transaction
            Transaction::create([
                'order_id' => $orderId,
                'user_id' => $order->user_id,
                'type' => 'refund',
                'amount' => $amount,
                'razorpay_payment_id' => $transaction->razorpay_payment_id,
                'razorpay_refund_id' => $refund->id,
                'status' => 'completed',
                'metadata' => json_encode(['reason' => $reason]),
            ]);

            return [
                'refund_id' => $refund->id,
                'amount' => $amount,
                'status' => $refund->status,
            ];
        } catch (Exception $e) {
            throw new Exception('Refund processing failed: ' . $e->getMessage());
        }
    }
}
