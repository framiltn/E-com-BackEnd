<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

echo "=== Product Images Analysis ===\n\n";

// Check products table
echo "First 5 products:\n";
$products = Product::select('id', 'name', 'images')->limit(5)->get();
foreach ($products as $product) {
    echo "ID: {$product->id} | Name: {$product->name}\n";
    echo "Images JSON: " . json_encode($product->images) . "\n";
    echo "---\n";
}

echo "\n=== Product Images Table ===\n";
$productImages = DB::table('product_images')
    ->select('id', 'product_id', 'url', 'is_primary')
    ->limit(10)
    ->get();

foreach ($productImages as $img) {
    echo "ID: {$img->id} | Product: {$img->product_id} | Primary: " . ($img->is_primary ? 'Yes' : 'No') . "\n";
    echo "URL: {$img->url}\n";
    echo "---\n";
}

echo "\n=== Product Image Count by Product ===\n";
$imageCounts = DB::table('product_images')
    ->select('product_id', DB::raw('COUNT(*) as count'))
    ->groupBy('product_id')
    ->limit(10)
    ->get();

foreach ($imageCounts as $count) {
    echo "Product {$count->product_id}: {$count->count} images\n";
}
