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
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $sort = $request->query('sort', 'newest');

        $query = Product::query()
            ->select('id', 'name', 'description', 'price', 'stock', 'category_id', 'brand', 'commission_level', 'seller_id', 'created_at')
            ->approved()
            ->with([
                'seller:id,name',
                'images:id,product_id,url,is_primary,alt_text'
            ])
            ->search($q)
            ->category($category)
            ->seller($sellerId)
            ->priceBetween($min, $max);

        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $paginator = $query->paginate($perPage)->appends($request->query());

        $data = $paginator->through(fn($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'category' => $product->category,
            'brand' => $product->brand,
            'commission_level' => $product->commission_level,
            'images' => $product->getAllImages(),
            'seller' => $product->seller ? [
                'id' => $product->seller->id,
                'name' => $product->seller->name,
            ] : null,
            'created_at' => $product->created_at->toISOString(),
        ]);

        return response()->json([
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'data' => $data,
        ]);
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
        $products = Product::where('seller_id', auth()->id())
            ->with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $products
        ]);
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
            ->with(['seller:id,name,email', 'images'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'category' => $product->category,
                'brand' => $product->brand,
                'commission_level' => $product->commission_level,
                'images' => $product->getAllImages(),
                'seller' => $product->seller ? [
                    'id' => $product->seller->id,
                    'name' => $product->seller->name,
                    'email' => $product->seller->email,
                ] : null,
                'created_at' => $product->created_at->toISOString(),
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
