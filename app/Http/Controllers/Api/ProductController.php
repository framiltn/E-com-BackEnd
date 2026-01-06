<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/products",
     *     tags={"Products"},
     *     summary="Browse all products",
     *     @OA\Parameter(name="q", in="query", description="Search term", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category", in="query", description="Category slug or ID", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="seller_id", in="query", description="Filter by seller ID", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="min_price", in="query", description="Minimum price", required=false, @OA\Schema(type="number")),
     *     @OA\Parameter(name="max_price", in="query", description="Maximum price", required=false, @OA\Schema(type="number")),
     *     @OA\Parameter(name="sort", in="query", description="Sort by: newest, price_asc, price_desc", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 12);
        $perPage = max(1, min(100, $perPage));

        $q = $request->query('q');
        $category = $request->query('category');
        $sellerId = $request->query('seller_id');
        $min = $request->filled('min_price') ? $request->input('min_price') : null;
        $max = $request->filled('max_price') ? $request->input('max_price') : null;
        $brand = $request->query('brand');
        $available = $request->query('availability');
        $sort = $request->query('sort', 'newest');

        $query = Product::query()
            ->select('id', 'name', 'price', 'stock', 'brand', 'seller_id', 'images')
            ->approved()
            ->search($q)
            ->category($category)
            ->seller($sellerId)
            ->priceBetween($min, $max)
            ->brand($brand)
            ->available($available);

        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->orderBy('id', 'desc'),
        };

        $paginator = $query->paginate($perPage);

        $data = $paginator->through(fn($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'brand' => $product->brand,
            'images' => $this->formatImages($product->images),
            'seller_id' => $product->seller_id,
        ]);

        // Get available brands based on current category and search
    // We create a fresh query to avoid interfering with the main query builder
    $brandQuery = Product::approved();
    
    if ($request->filled('category')) {
        $brandQuery->category($request->query('category'));
    }
    
    if ($request->filled('q')) {
        $brandQuery->search($request->query('q'));
    }

    if ($request->filled('min_price') || $request->filled('max_price')) {
         $brandQuery->priceBetween($min, $max);
    }
    
    // We don't filter by brand here, because we want to show other available brands 
    // even if one is selected (standard e-com behavior)
    
    $brands = \Illuminate\Support\Facades\Cache::remember(
        'brands_' . md5(json_encode($request->all())), 
        300, 
        function () use ($brandQuery) {
            return $brandQuery->distinct()->pluck('brand')->filter()->values();
        }
    );

        return response()->json([
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'brands' => $brands,
            ],
            'data' => $data,
        ]);
    }

    private function formatImages($images)
    {
        if (empty($images)) return [];
        
        $formatted = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                $url = is_string($image) ? $image : ($image['url'] ?? $image['path'] ?? '');
                if ($url) {
                    $formatted[] = $this->getFullImageUrl($url);
                }
            }
        }
        
        return $formatted;
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

    /**
     * @OA\Get(
     *     path="/seller/products",
     *     tags={"Products"},
     *     summary="Get seller's products",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of seller's products",
     *         @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(type="object")))
     *     )
     * )
     */
    public function sellerIndex(Request $request)
    {
        try {
            $products = Product::where('seller_id', auth()->id())
                ->with(['category', 'productImages'])
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $products->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'category' => $product->category,
                'brand' => $product->brand,
                'commission_level' => $product->commission_level,
                'status' => $product->status,
                'images' => $product->getAllImages(),
                'created_at' => $product->created_at->toISOString(),
            ]);

            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('SellerIndex Error: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     tags={"Products"},
     *     summary="Get product details",
     *     @OA\Parameter(name="id", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show($id)
    {
        $product = Product::approved()
            ->select('id', 'name', 'description', 'price', 'stock', 'brand', 'seller_id', 'images')
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'brand' => $product->brand,
                'images' => $this->formatImages($product->images),
                'seller_id' => $product->seller_id,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/seller/products",
     *     tags={"Products"},
     *     summary="Create a new product (Seller)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","price","stock","category_id"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="stock", type="integer"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="brand", type="string"),
     *             @OA\Property(property="commission_level", type="string", enum={"6-4-2","9-6-3","12-8-4"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created",
     *         @OA\JsonContent(@OA\Property(property="data", type="object"))
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string',
            'commission_level' => 'required|in:6-4-2,9-6-3,12-8-4',
        ]);

        $product = Product::create([
            ...$validated,
            'seller_id' => auth()->id(),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/seller/products/{id}",
     *     tags={"Products"},
     *     summary="Update a product (Seller)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="stock", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated",
     *         @OA\JsonContent(@OA\Property(property="data", type="object"))
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $product = Product::where('seller_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'brand' => 'nullable|string',
            'commission_level' => 'sometimes|in:6-4-2,9-6-3,12-8-4',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/seller/products/{id}",
     *     tags={"Products"},
     *     summary="Delete a product (Seller)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted"
     *     )
     * )
     */
    public function destroy($id)
    {
        $product = Product::where('seller_id', auth()->id())->findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
