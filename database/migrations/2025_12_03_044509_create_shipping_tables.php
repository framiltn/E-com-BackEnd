<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Shipping zones
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('states'); // Array of state codes
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Shipping rates
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('shipping_zones')->nullOnDelete();
            $table->enum('type', ['free', 'flat', 'calculated'])->default('flat');
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('min_order_value', 10, 2)->nullable();
            $table->boolean('use_shiprocket')->default(false);
            $table->timestamps();
        });

        // Shipments
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_order_id')->constrained()->cascadeOnDelete();
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('shiprocket_order_id')->nullable();
            $table->string('shiprocket_shipment_id')->nullable();
            $table->string('awb_code')->nullable();
            $table->enum('status', ['pending', 'picked', 'in_transit', 'delivered', 'cancelled'])->default('pending');
            $table->text('label_url')->nullable();
            $table->json('tracking_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
    }
};
