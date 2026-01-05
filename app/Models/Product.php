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

    // Get all images (both JSON and relationship)
    public function getAllImages()
    {
        $images = [];
        
        // Add images from relationship
        $relationshipImages = $this->productImages()->get();
        if ($relationshipImages->count() > 0) {
            foreach ($relationshipImages as $image) {
                $images[] = [
                    'url' => $this->getFullImageUrl($image->url),
                    'is_primary' => $image->is_primary,
                    'alt_text' => $image->alt_text
                ];
            }
        }
        
        // Add images from JSON column if no relationship images
        if (empty($images) && !empty($this->images)) {
            $jsonImages = $this->images;
            if (is_array($jsonImages)) {
                foreach ($jsonImages as $imageItem) {
                    $url = is_array($imageItem) ? ($imageItem['url'] ?? $imageItem['path'] ?? reset($imageItem)) : $imageItem;
                    
                    if (is_string($url)) {
                        $images[] = [
                            'url' => $this->getFullImageUrl($url),
                            'is_primary' => true,
                            'alt_text' => $this->name
                        ];
                    }
                }
            }
        }
        
        return $images;
    }

    private function getFullImageUrl($url)
    {
        if (empty($url)) return null;
        if (str_starts_with($url, 'http')) return $url;
        
        // Remove leading slash and storage prefix if present
        $url = ltrim($url, '/');
        
        // If it's already in storage or explicitly in images public folder
        if (str_starts_with($url, 'storage/') || str_starts_with($url, 'images/')) {
            return url($url);
        }
        
        return url('storage/' . $url);
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

    // Search by name / description
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $term = trim($term);

        // Use ILIKE for Postgres (case insensitive), LIKE for others
        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return $query->where(function ($q) use ($term, $operator) {
            $q->where('name', $operator, "%{$term}%")
              ->orWhere('description', $operator, "%{$term}%");
        });
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
