<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerApplicationController extends Controller
{
    // user submits seller application
    public function apply(Request $request)
    {
        $data = $request->validate([
            'store_name'     => 'required|string|max:255',
            'business_name'  => 'required|string|max:255',
            'business_type'  => 'required|string',
            'pan_number'     => 'required|string',
            'mobile'         => 'required|string',
            'gst_number'     => 'nullable|string',
            'address'        => 'required|string',
            'about_store'    => 'nullable|string',
            'instagram'      => 'nullable|string',
            'facebook'       => 'nullable|string',
            'website'        => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();

        $existing = SellerApplication::where('user_id', Auth::id())
                    ->whereIn('status', ['pending', 'approved'])
                    ->first();

        if ($existing) {
            $message = $existing->status === 'approved' 
                ? 'You are already a registered seller.' 
                : 'Your seller application is already under review.';
                
            return response()->json([
                'message' => $message
            ], 400);
        }

        SellerApplication::create($data);

        return response()->json([
            'message' => 'Seller application submitted successfully!'
        ]);
    }
}
