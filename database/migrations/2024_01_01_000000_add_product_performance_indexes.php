<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index(['category_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['price', 'status']);
            $table->index(['brand', 'status']);
            $table->index(['stock', 'status']);
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['category_id', 'status']);
            $table->dropIndex(['seller_id', 'status']);
            $table->dropIndex(['price', 'status']);
            $table->dropIndex(['brand', 'status']);
            $table->dropIndex(['stock', 'status']);
        });
    }
};