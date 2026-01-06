<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Attempting to create indexes...\n";

try {
    Schema::table('products', function (Blueprint $table) {
        // Try one by one to isolate the issue
        
        if (!collect(Schema::getIndexes('products'))->contains('name', 'products_category_id_index')) {
            echo "Adding index: category_id\n";
            $table->index('category_id');
        } else {
            echo "Index category_id already exists\n";
        }

        if (!collect(Schema::getIndexes('products'))->contains('name', 'products_price_index')) {
            echo "Adding index: price\n";
            $table->index('price');
        } else {
             echo "Index price already exists\n";
        }
        
    });
    
     Schema::table('products', function (Blueprint $table) {
        if (!collect(Schema::getIndexes('products'))->contains('name', 'products_brand_index')) {
            echo "Adding index: brand\n";
            $table->index('brand');
        }
    });

     Schema::table('products', function (Blueprint $table) {
        if (!collect(Schema::getIndexes('products'))->contains('name', 'products_status_index')) {
            echo "Adding index: status\n";
            $table->index('status');
        }
     });

      Schema::table('products', function (Blueprint $table) {
        if (!collect(Schema::getIndexes('products'))->contains('name', 'products_stock_index')) {
            echo "Adding index: stock\n";
            $table->index('stock');
        }
      });

    echo "Indexes created successfully via script.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
