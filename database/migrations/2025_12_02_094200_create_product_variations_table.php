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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            $table->string('sku')->unique();
            $table->string('name'); // e.g., "Red - Large"
            
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            
            // JSON field for attributes like {"color": "red", "size": "L"}
            $table->json('attributes')->nullable();
            
            // Optional variation-specific image
            $table->string('image')->nullable();
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
