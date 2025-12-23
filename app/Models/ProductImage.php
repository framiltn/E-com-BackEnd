<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'url', 'alt_text', 'order', 'is_primary'];

    protected $casts = [
        'order' => 'integer',
        'is_primary' => 'integer', // Use integer for PostgreSQL compatibility
    ];

    // Mutator to convert boolean to integer (0 or 1)
    public function setIsPrimaryAttribute($value)
    {
        $this->attributes['is_primary'] = $value ? 1 : 0;
    }

    // Accessor to convert integer back to boolean when reading
    public function getIsPrimaryAttribute($value)
    {
        return (bool)$value;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
