<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/categories",
     *     tags={"Categories"},
     *     summary="Get all categories (hierarchical)",
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/categories/{id}",
     *     tags={"Categories"},
     *     summary="Get category details",
     *     @OA\Parameter(name="id", in="path", description="Category ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function show($id)
    {
        $category = Category::where('is_active', true)
            ->with(['products' => function ($query) {
                $query->where('status', 'approved')
                    ->with('seller:id,name')
                    ->limit(50); // Limit products per category
            }])
            ->findOrFail($id);

        return response()->json($category);
    }

    /**
     * @OA\Get(
     *     path="/categories/slug/{slug}",
     *     tags={"Categories"},
     *     summary="Get category by slug",
     *     @OA\Parameter(name="slug", in="path", description="Category Slug", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function bySlug($slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->with(['products' => function ($query) {
                $query->where('status', 'approved')
                    ->with('seller:id,name');
            }])
            ->firstOrFail();

        return response()->json($category);
    }
}
