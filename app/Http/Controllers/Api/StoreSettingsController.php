<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreSettingsController extends Controller
{
    public function show()
    {
        $settings = StoreSettings::firstOrCreate(
            ['seller_id' => auth()->id()],
            ['shipping_type' => 'shiprocket']
        );

        return response()->json(['data' => $settings]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'brand_story' => 'nullable|string',
            'instagram' => 'nullable|url',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'website' => 'nullable|url',
            'shipping_type' => 'required|in:shiprocket,self',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'flat_shipping_rate' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'return_policy' => 'nullable|string',
        ]);

        $settings = StoreSettings::updateOrCreate(
            ['seller_id' => auth()->id()],
            $validated
        );

        return response()->json(['message' => 'Settings updated', 'data' => $settings]);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $settings = StoreSettings::firstOrCreate(['seller_id' => auth()->id()]);

        if ($settings->logo_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $settings->logo_url));
        }

        $path = $request->file('logo')->store('logos', 'public');
        $settings->update(['logo_url' => Storage::url($path)]);

        return response()->json(['message' => 'Logo uploaded', 'url' => $settings->logo_url]);
    }

    public function uploadBanner(Request $request)
    {
        $request->validate(['banner' => 'required|image|max:5120']);

        $settings = StoreSettings::firstOrCreate(['seller_id' => auth()->id()]);

        if ($settings->banner_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $settings->banner_url));
        }

        $path = $request->file('banner')->store('banners', 'public');
        $settings->update(['banner_url' => Storage::url($path)]);

        return response()->json(['message' => 'Banner uploaded', 'url' => $settings->banner_url]);
    }
}
