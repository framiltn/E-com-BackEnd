<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Page;
use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CMSController extends Controller
{
    // Banners
    public function getBanners()
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json(['data' => $banners]);
    }

    public function createBanner(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:5120',
            'link' => 'nullable|url',
            'order' => 'nullable|integer',
        ]);

        $path = $request->file('image')->store('banners', 'public');

        $banner = Banner::create([
            'title' => $validated['title'],
            'image_url' => Storage::url($path),
            'link' => $validated['link'] ?? null,
            'order' => $validated['order'] ?? 0,
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Banner created', 'data' => $banner], 201);
    }

    public function updateBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'link' => 'nullable|url',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image_url));
            $path = $request->file('image')->store('banners', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $banner->update($validated);

        return response()->json(['message' => 'Banner updated', 'data' => $banner]);
    }

    public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);
        Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image_url));
        $banner->delete();

        return response()->json(['message' => 'Banner deleted']);
    }

    // Pages
    public function getPages()
    {
        $pages = Page::where('is_published', true)->get();
        return response()->json(['data' => $pages]);
    }

    public function getPage($slug)
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();
        return response()->json(['data' => $page]);
    }

    public function createPage(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:pages,slug',
            'content' => 'required|string',
            'meta_description' => 'nullable|string',
        ]);

        $page = Page::create([...$validated, 'is_published' => true]);

        return response()->json(['message' => 'Page created', 'data' => $page], 201);
    }

    public function updatePage(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'meta_description' => 'nullable|string',
            'is_published' => 'sometimes|boolean',
        ]);

        $page->update($validated);

        return response()->json(['message' => 'Page updated', 'data' => $page]);
    }

    // FAQs
    public function getFAQs()
    {
        $faqs = FAQ::where('is_active', true)->orderBy('order')->get();
        return response()->json(['data' => $faqs]);
    }

    public function createFAQ(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'category' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $faq = FAQ::create([...$validated, 'is_active' => true]);

        return response()->json(['message' => 'FAQ created', 'data' => $faq], 201);
    }

    public function updateFAQ(Request $request, $id)
    {
        $faq = FAQ::findOrFail($id);

        $validated = $request->validate([
            'question' => 'sometimes|string',
            'answer' => 'sometimes|string',
            'category' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $faq->update($validated);

        return response()->json(['message' => 'FAQ updated', 'data' => $faq]);
    }

    public function deleteFAQ($id)
    {
        FAQ::findOrFail($id)->delete();
        return response()->json(['message' => 'FAQ deleted']);
    }
}
