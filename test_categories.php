<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "=== Testing Category Filtering ===\n\n";

// Test fashion category
echo "1. Testing Fashion Category:\n";
$response = Http::get('http://localhost:8000/api/products?category=fashion&per_page=5');
if ($response->successful()) {
    $data = $response->json();
    echo "Found {$data['meta']['total']} products\n";
    foreach ($data['data'] as $product) {
        echo "  - {$product['name']} (ID: {$product['id']})\n";
    }
} else {
    echo "Error: " . $response->status() . "\n";
}

echo "\n2. Testing Grocery Category:\n";
$response = Http::get('http://localhost:8000/api/products?category=grocery&per_page=5');
if ($response->successful()) {
    $data = $response->json();
    echo "Found {$data['meta']['total']} products\n";
    foreach ($data['data'] as $product) {
        echo "  - {$product['name']} (ID: {$product['id']})\n";
    }
} else {
    echo "Error: " . $response->status() . "\n";
}

echo "\n3. Testing All Products (no filter):\n";
$response = Http::get('http://localhost:8000/api/products?per_page=5');
if ($response->successful()) {
    $data = $response->json();
    echo "Found {$data['meta']['total']} products\n";
    foreach ($data['data'] as $product) {
        echo "  - {$product['name']} (ID: {$product['id']})\n";
    }
} else {
    echo "Error: " . $response->status() . "\n";
}
