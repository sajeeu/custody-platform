<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Withdrawal;

class AdminWithdrawalController extends Controller
{
    public function allocated(Request $request)
    {
        // 1. Must be logged in
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // 2. Must be ADMIN
        if ($user->role !== 'ADMIN') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden (admin only).',
            ], 403);
        }

        $status = 'PENDING'; // force queue to be truly PENDING-only

        $withdrawals = Withdrawal::where('storage_type', 'ALLOCATED')
            ->where('status', $status)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $withdrawals,
        ]);
            }
}
