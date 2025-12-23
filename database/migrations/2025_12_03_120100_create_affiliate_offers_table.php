<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['product', 'brand']);
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('min_sales_volume', 10, 2);
            $table->decimal('achieved_volume', 10, 2)->default(0);
            $table->enum('status', ['pending', 'achieved', 'claimed', 'expired'])->default('pending');
            $table->timestamps();
        });

        Schema::create('shared_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('shared_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_to')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_coupons');
        Schema::dropIfExists('affiliate_offers');
    }
};
