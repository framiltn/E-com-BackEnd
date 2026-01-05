<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Affiliate;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
    /**
     * @OA\Get(
     *     path="/affiliate",
     *     tags={"Affiliate"},
     *     summary="Get affiliate profile and earnings",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Affiliate profile",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="referral_code", type="string"),
     *             @OA\Property(property="earnings", type="number", format="float")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $affiliate = Affiliate::where('user_id', $request->user()->id)->first();
        
        if (!$affiliate) {
            return response()->json(['message' => 'Not an affiliate'], 404);
        }

        return response()->json($affiliate);
    }

    public function join(Request $request, \App\Services\CommissionService $commissionService)
    {
        $user = $request->user();
        
        $affiliate = Affiliate::where('user_id', $user->id)->first();
        if ($affiliate) {
            return response()->json(['message' => 'Already an affiliate'], 400); 
        }

        // Check if user has at least one order (as per requirements)
        $hasOrders = \App\Models\Order::where('user_id', $user->id)->exists();
        if (!$hasOrders) {
             return response()->json(['message' => 'You must have placed at least one order to become an affiliate.'], 403);
        }

        $affiliate = $commissionService->autoEnrollAffiliate($user);

        return response()->json([
            'message' => 'Welcome to the Affiliate Program!',
            'affiliate' => $affiliate
        ]);
    }

    public function referrals(Request $request)
    {
        $referrals = Referral::where('referrer_id', $request->user()->id)
            ->with('referee')
            ->paginate(50);
        return response()->json($referrals);
    }

    public function tree(Request $request)
    {
        $user = $request->user();
        
        // Use PostgreSQL Recursive CTE for efficient tree traversal
        if (DB::connection()->getDriverName() === 'pgsql') {
            $tree = DB::select("
                WITH RECURSIVE affiliate_tree AS (
                    -- Base case: direct referrals (level 1)
                    SELECT 
                        r.id,
                        r.referee_id,
                        r.referrer_id,
                        u.name,
                        u.email,
                        1 as level
                    FROM referrals r
                    JOIN users u ON r.referee_id = u.id
                    WHERE r.referrer_id = ?
                    
                    UNION ALL
                    
                    -- Recursive case: get children of children (up to level 3)
                    SELECT 
                        r.id,
                        r.referee_id,
                        r.referrer_id,
                        u.name,
                        u.email,
                        at.level + 1
                    FROM referrals r
                    JOIN users u ON r.referee_id = u.id
                    JOIN affiliate_tree at ON r.referrer_id = at.referee_id
                    WHERE at.level < 3
                )
                SELECT * FROM affiliate_tree ORDER BY level, id
            ", [$user->id]);
            
            return response()->json([
                'tree' => $tree,
                'total_referrals' => count($tree)
            ]);
        }
        
        // Fallback for non-PostgreSQL
        $level1 = Referral::where('referrer_id', $user->id)->with('referee')->get();
        
        return response()->json([
            'level_1' => $level1,
        ]);
    }
}
