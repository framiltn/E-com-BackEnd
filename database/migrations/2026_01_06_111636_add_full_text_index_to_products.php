<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('database.default') === 'pgsql') {
            DB::statement("CREATE INDEX IF NOT EXISTS products_fulltext_idx ON products USING GIN (to_tsvector('english', name || ' ' || coalesce(description, '')))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'pgsql') {
             DB::statement("DROP INDEX IF EXISTS products_fulltext_idx");
        }
    }
};
