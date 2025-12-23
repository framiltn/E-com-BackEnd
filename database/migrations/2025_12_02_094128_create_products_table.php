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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');

        $table->string('name');
        $table->text('description')->nullable();

        $table->decimal('price', 10, 2);
        $table->integer('stock')->default(0);

        // Minimum price rule (Rs. 1200)
        $table->boolean('below_minimum_price')->default(false);

        // Commission levels
        $table->enum('commission_level', ['6-4-2', '9-6-3', '12-8-4']);

        $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
        $table->string('brand')->nullable();

        $table->json('images')->nullable();

        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
