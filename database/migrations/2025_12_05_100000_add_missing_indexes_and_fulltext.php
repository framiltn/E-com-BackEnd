<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add only missing non-foreign key indexes or composite indexes
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index('read_at');
            $table->index(['user_id', 'read_at']); // Composite for unread queries
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->index('is_active');
            $table->index(['is_active', 'valid_from', 'valid_to']); // Composite for active coupon queries
        });

        // PostgreSQL Full-Text Search Indexes
        if (DB::connection()->getDriverName() === 'pgsql') {
            // GIN index for product name and description search
            DB::statement('CREATE INDEX products_name_gin_idx ON products USING GIN (to_tsvector(\'english\', name))');
            DB::statement('CREATE INDEX products_description_gin_idx ON products USING GIN (to_tsvector(\'english\', description))');
            
            // GIN index for reviews comment search
            DB::statement('CREATE INDEX reviews_comment_gin_idx ON reviews USING GIN (to_tsvector(\'english\', comment))');
            
            // JSONB index for notifications data
            DB::statement('CREATE INDEX notifications_data_gin_idx ON notifications USING GIN (data)');
        }
    }

    public function down(): void
    {
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['seller_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['seller_order_id']);
            $table->dropIndex(['product_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['read_at']);
            $table->dropIndex(['user_id', 'read_at']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['is_active', 'valid_from', 'valid_to']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS products_name_gin_idx');
            DB::statement('DROP INDEX IF EXISTS products_description_gin_idx');
            DB::statement('DROP INDEX IF EXISTS reviews_comment_gin_idx');
            DB::statement('DROP INDEX IF EXISTS notifications_data_gin_idx');
        }
    }
};
