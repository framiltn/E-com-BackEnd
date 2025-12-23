<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerApplication;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Seller Applications
    public function pendingApplications()
    {
        $applications = SellerApplication::where('status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($applications);
    }

    public function approveApplication(Request $request, $id)
    {
        $application = SellerApplication::findOrFail($id);
        
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $application->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'admin_notes' => $validated['notes'] ?? null,
        ]);

        // Update user role to seller
        $user = $application->user;
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        // Role is assigned via SellerApplication model event
        // $user->assignRole('seller');
        
        \Log::info('User roles after approval:', ['roles' => $user->getRoleNames()->toArray()]);

        return response()->json([
            'message' => 'Application approved successfully',
            'user_roles' => $user->getRoleNames()
        ]);
    }

    public function rejectApplication(Request $request, $id)
    {
        $application = SellerApplication::findOrFail($id);
        
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $application->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'admin_notes' => $validated['reason'],
        ]);

        return response()->json(['message' => 'Application rejected']);
    }

    // Product Approvals
    public function pendingProducts()
    {
        $products = Product::where('status', 'pending')
            ->with('seller')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($products);
    }

    public function approveProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['status' => 'approved']);

        return response()->json(['message' => 'Product approved successfully']);
    }

    public function rejectProduct(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $product = Product::findOrFail($id);
        $product->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
        ]);

        return response()->json(['message' => 'Product rejected']);
    }

    // Dashboard Analytics
    public function dashboard()
    {
        $stats = [
            'total_sales' => \App\Models\Order::where('order_status', 'delivered')->sum('total_amount'),
            'total_orders' => \App\Models\Order::count(),
            'total_users' => User::count(),
            'total_sellers' => User::role('seller')->count(),
            'total_buyers' => User::role('buyer')->count(),
            'pending_applications' => SellerApplication::where('status', 'pending')->count(),
            'pending_products' => Product::where('status', 'pending')->count(),
            'pending_disputes' => \App\Models\Dispute::where('status', 'open')->count(),
            'total_products' => Product::count(),
            'approved_products' => Product::where('status', 'approved')->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    // User Management
    public function users()
    {
        $users = User::with('roles')->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($users);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    // Seller Management
    public function sellers()
    {
        $sellers = User::role('seller')->with('roles')->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($sellers);
    }

    public function createSeller(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('seller');

        return response()->json(['message' => 'Seller created successfully', 'data' => $user]);
    }

    public function updateSeller(Request $request, $id)
    {
        $user = User::role('seller')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user->update($validated);

        return response()->json(['message' => 'Seller updated successfully', 'data' => $user]);
    }

    public function deleteSeller($id)
    {
        $user = User::role('seller')->findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Seller deleted successfully']);
    }
}
