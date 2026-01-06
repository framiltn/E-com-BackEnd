<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "=== Category and Products Check ===\n\n";

// Get all categories
$categories = Category::all();
echo "Categories:\n";
foreach ($categories as $cat) {
    $count = Product::where('category_id', $cat->id)->count();
    echo "  - {$cat->name} (slug: {$cat->slug}, ID: {$cat->id}) - {$count} products\n";
}

echo "\n\nFashion Products:\n";
$fashion = Category::where('slug', 'fashion')->first();
if ($fashion) {
    $products = Product::where('category_id', $fashion->id)->get(['id', 'name']);
    foreach ($products as $p) {
        echo "  - {$p->name} (ID: {$p->id})\n";
    }
} else {
    echo "Fashion category not found!\n";
}

echo "\n\nGrocery Products:\n";
$grocery = Category::where('slug', 'grocery')->first();
if ($grocery) {
    $products = Product::where('category_id', $grocery->id)->get(['id', 'name']);
    foreach ($products as $p) {
        echo "  - {$p->name} (ID: {$p->id})\n";
    }
} else {
    echo "Grocery category not found!\n";
}
