<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function uploadProductImage(Request $request, $productId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $product = Product::where('seller_id', auth()->id())->findOrFail($productId);

        $path = $request->file('image')->store('products', 'public');
        $url = Storage::url($path);

        // Get existing images from JSON column
        $images = $product->images ?? [];
        
        // Add new image with metadata
        $newImage = [
            'id' => count($images) + 1,
            'url' => $url,
            'is_primary' => count($images) === 0, // First image is primary
        ];
        
        $images[] = $newImage;
        
        // Update product with new images array
        $product->update(['images' => $images]);

        return response()->json([
            'message' => 'Image uploaded successfully',
            'data' => $newImage,
        ], 201);
    }

    public function deleteProductImage($productId, $imageId)
    {
        $product = Product::where('seller_id', auth()->id())->findOrFail($productId);
        
        $images = $product->images ?? [];
        $imageIndex = array_search($imageId, array_column($images, 'id'));
        
        if ($imageIndex === false) {
            return response()->json(['message' => 'Image not found'], 404);
        }
        
        $imageUrl = $images[$imageIndex]['url'];
        
        // Delete file from storage
        $path = str_replace('/storage/', '', $imageUrl);
        Storage::disk('public')->delete($path);

        // Remove image from array
        array_splice($images, $imageIndex, 1);
        
        // Update product
        $product->update(['images' => $images]);

        return response()->json(['message' => 'Image deleted successfully']);
    }

    public function setPrimaryImage($productId, $imageId)
    {
        $product = Product::where('seller_id', auth()->id())->findOrFail($productId);
        
        $images = $product->images ?? [];
        
        // Set all images to non-primary
        foreach ($images as &$image) {
            $image['is_primary'] = false;
        }
        
        // Find and set the selected image as primary
        $imageIndex = array_search($imageId, array_column($images, 'id'));
        
        if ($imageIndex !== false) {
            $images[$imageIndex]['is_primary'] = true;
        }
        
        // Update product
        $product->update(['images' => $images]);

        return response()->json(['message' => 'Primary image set successfully']);
    }
}
