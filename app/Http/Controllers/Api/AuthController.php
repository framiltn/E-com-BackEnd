<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request, \App\Services\CommissionService $commissionService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/'
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Default role for new users
        $user->assignRole('buyer');
        $user->assignRole('affiliate');

        // Auto-enroll as affiliate
        $commissionService->autoEnrollAffiliate($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="redirect", type="string")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
{
    $validated = $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    // Rate limiting: max 5 login attempts per minute per email
    $key = 'login_attempts:' . $validated['email'];
    $attempts = \Illuminate\Support\Facades\Cache::get($key, 0);
    
    if ($attempts >= 5) {
        throw ValidationException::withMessages([
            'email' => 'Too many login attempts. Please try again in 1 minute.',
        ]);
    }

    $user = User::where('email', $validated['email'])->first();

    if (! $user || ! Hash::check($validated['password'], $user->password)) {
        // Increment failed attempts
        \Illuminate\Support\Facades\Cache::put($key, $attempts + 1, now()->addMinute());
        
        throw ValidationException::withMessages([
            'email' => 'Invalid login credentials.',
        ]);
    }

    // Clear attempts on successful login
    \Illuminate\Support\Facades\Cache::forget($key);

    $token = $user->createToken('auth_token')->plainTextToken;

    $role = $user->getRoleNames()->first(); // first assigned role

    $redirectUrl = match ($role) {
        'admin'     => '/admin',
        'seller'    => '/seller/dashboard',
        'affiliate' => '/affiliate/dashboard',
        default     => '/dashboard', // buyer
    };

    return response()->json([
        'message'  => 'Login successful',
        'token'    => $token,
        'user'     => $user,
        'role'     => $role,
        'redirect' => $redirectUrl,
    ]);
}


    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);
        
        // Append role to user object
        $roles = $user->getRoleNames();
        if ($roles->contains('admin')) {
            $user->role = 'admin';
        } elseif ($roles->contains('seller')) {
            $user->role = 'seller';
        } else {
            $user->role = 'buyer';
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        // Optional: Delete related data (orders, products, etc.) or soft delete
        // For now, we'll just delete the user record.
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }
}
