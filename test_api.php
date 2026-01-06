<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "=== Testing Products API ===\n\n";

$response = Http::get('http://localhost:8000/api/products?per_page=3');

if ($response->successful()) {
    $data = $response->json();
    
    echo "First 3 products:\n";
    foreach ($data['data'] as $product) {
        echo "\nProduct ID: {$product['id']}\n";
        echo "Name: {$product['name']}\n";
        echo "Images: " . json_encode($product['images']) . "\n";
        echo "---\n";
    }
} else {
    echo "Error: " . $response->status() . "\n";
    echo $response->body() . "\n";
}
