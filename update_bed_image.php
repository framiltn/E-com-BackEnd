<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

echo "=== Updating King Size Bed Image ===\n\n";

// Find the King Size Solid Wood Bed product
$product = Product::where('name', 'King Size Solid Wood Bed')->first();

if ($product) {
    echo "Found product: {$product->name} (ID: {$product->id})\n";
    echo "Current images: " . json_encode($product->images) . "\n\n";
    
    // Update the image
    $product->images = ['/images/products/home_bed_1767695027776.png'];
    $product->save();
    
    echo "Updated images: " . json_encode($product->images) . "\n";
    echo "✓ Image updated successfully!\n";
} else {
    echo "Product not found. You may need to reseed the database.\n";
}
