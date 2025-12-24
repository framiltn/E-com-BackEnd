<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
            $table->index(['seller_id', 'status']);
        });

        // Orders indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('order_status');
            $table->index('created_at');
            $table->index(['user_id', 'order_status']);
        });

        // Users indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('created_at');
        });

        // Reviews indexes
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index(['product_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['seller_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex(['product_id', 'created_at']);
            });
        }
    }
};
