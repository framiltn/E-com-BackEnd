<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // These are already in 2024_01_01 migration
            // $table->index('status');
            // $table->index('created_at');
            
            $table->index('seller_id');
            $table->index('category_id');
            $table->index('price'); // For price filtering
            $table->index(['status', 'created_at']); // Composite for approved products listing
        });

        Schema::table('orders', function (Blueprint $table) {
            // $table->index('status'); // already in 2024_01_01
            // $table->index('created_at'); // already in 2024_01_01
            
            $table->index('user_id');
            $table->index('payment_status');
            $table->index('order_status');
            $table->index(['user_id', 'created_at']); // Composite for user order history
        });

        Schema::table('seller_applications', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index(['user_id', 'product_id']); // Composite for cart lookups
        });

        Schema::table('affiliates', function (Blueprint $table) {
            $table->index('user_id');
            // $table->index('referrer_id'); // If referrer_id exists
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->index('product_id');
            $table->index(['product_id', 'is_primary']); // For primary image lookup
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['seller_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['order_status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('seller_applications', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('cart', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['referrer_id']);
        });
    }
};
