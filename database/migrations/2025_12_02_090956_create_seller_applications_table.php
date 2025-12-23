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
    Schema::create('seller_applications', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        // Seller details
        $table->string('store_name');
        $table->string('business_name');
        $table->string('business_type');
        $table->string('pan_number');
        $table->string('brand_logo')->nullable();
        $table->string('gst_number')->nullable();
        $table->string('mobile');
        $table->string('address');
        $table->text('about_store')->nullable();
        $table->string('instagram')->nullable();
        $table->string('facebook')->nullable();
        $table->string('website')->nullable();

        // Status
        $table->enum('status', ['pending', 'approved', 'rejected'])
              ->default('pending');
        $table->timestamp('reviewed_at')->nullable();
        $table->text('admin_notes')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_applications');
    }
};
