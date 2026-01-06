<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking indexes via raw SQL...\n";

try {
    $results = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'products'");
    $indexes = array_map(fn($r) => $r->indexname, $results);
    
    echo "Existing indexes:\n";
    foreach ($indexes as $idx) {
        echo "- $idx\n";
    }

    if (!in_array('products_fulltext_idx', $indexes)) {
        echo "Creating fulltext index...\n";
         DB::statement("CREATE INDEX products_fulltext_idx ON products USING GIN (to_tsvector('english', name || ' ' || coalesce(description, '')))");
         echo "Created.\n";
    } else {
        echo "products_fulltext_idx already exists.\n";
    }
    
    // Check performance indexes too
    $perfIndexes = ['products_category_id_index', 'products_price_index', 'products_brand_index'];
    foreach ($perfIndexes as $idx) {
        if (!in_array($idx, $indexes)) {
             // Try to find if maybe it has a different name (e.g. products_category_id_index)
             // Default laravel name is table_column_index
             echo "Warning: $idx might be missing. Attempting to add...\n";
             // Extract column name
             $col = str_replace(['products_', '_index'], '', $idx);
             try {
                Schema::table('products', function ($table) use ($col) {
                    $table->index($col);
                });
                echo "Added index for $col\n";
             } catch(\Exception $e) {
                 echo "Failed to add $col: " . $e->getMessage() . "\n";
             }
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
