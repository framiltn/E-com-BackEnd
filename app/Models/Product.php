<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'seller_id',
        'name',
        'description',
        'price',
        'stock',
        'category_id',
        'brand',
        'images',
        'commission_level',
        'status',
        'below_minimum_price',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'below_minimum_price' => 'boolean',
    ];

    // Relationships
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function getAllImages()
    {
        if ($this->relationLoaded('productImages') && $this->productImages->isNotEmpty()) {
            return $this->productImages->map(fn($img) => [
                'url' => $this->getFullImageUrl($img->url),
                'is_primary' => $img->is_primary,
                'alt_text' => $img->alt_text
            ])->toArray();
        }
        
        if (!empty($this->images) && is_array($this->images)) {
            return collect($this->images)->map(fn($img) => [
                'url' => $this->getFullImageUrl(is_array($img) ? ($img['url'] ?? $img['path'] ?? '') : $img),
                'is_primary' => true,
                'alt_text' => $this->name
            ])->filter(fn($img) => !empty($img['url']))->values()->toArray();
        }
        
        return [];
    }

    private function getFullImageUrl($url)
    {
        if (empty($url)) return null;
        if (str_starts_with($url, 'http')) return $url;
        
        $url = ltrim($url, '/');
        
        if (str_starts_with($url, 'images/')) {
            return url($url);
        }
        
        return url('images/' . $url);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Only approved products
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) return $query;
        
        if (DB::connection()->getDriverName() === 'pgsql') {
            $term = trim($term);
            return $query->whereRaw("to_tsvector('english', name || ' ' || coalesce(description, '')) @@ plainto_tsquery('english', ?)", [$term]);
        }
        
        $term = '%' . trim($term) . '%';
        $operator = 'like';
        
        return $query->where('name', $operator, $term)
                    ->orWhere('description', $operator, $term);
    }

    // Filter by category
    public function scopeCategory($query, $category)
    {
        if (blank($category)) return $query;
        
        // Check if category is numeric (ID) or string (slug)
        if (is_numeric($category)) {
            return $query->where('category_id', $category);
        }
        
        // If it's a slug, join with categories table
        return $query->whereHas('category', function($q) use ($category) {
            $q->where('slug', $category);
        });
    }

    // Filter by seller id
    public function scopeSeller($query, $sellerId)
    {
        if (blank($sellerId)) return $query;
        return $query->where('seller_id', $sellerId);
    }

    // Price range
    public function scopePriceBetween($query, $min, $max)
    {
        if ($min === null && $max === null) return $query;
        if ($min !== null) $query->where('price', '>=', $min);
        if ($max !== null) $query->where('price', '<=', $max);
        return $query;
    }

    // Filter by Brand (supports comma-separated string or array)
    public function scopeBrand($query, $brands)
    {
        if (blank($brands)) return $query;
        $brandList = is_array($brands) ? $brands : explode(',', $brands);
        return $query->whereIn('brand', $brandList);
    }

    // Filter by Availability
    public function scopeAvailable($query, $available)
    {
        if ($available === 'true' || $available === true || $available === '1') {
            return $query->where('stock', '>', 0);
        }
        return $query;
    }

    // Helper: Get average rating
    public function averageRating()
    {
        return $this->reviews()->where('status', 'approved')->avg('rating') ?? 0;
    }

    // Helper: Get primary image
    public function primaryImage()
    {
        return $this->productImages()->where('is_primary', true)->first() 
            ?? $this->productImages()->first();
    }

    // Helper: Check if in stock
    public function inStock()
    {
        if ($this->variations()->exists()) {
            return $this->variations()->where('is_active', true)->sum('stock') > 0;
        }
        return $this->stock > 0;
    }
}
