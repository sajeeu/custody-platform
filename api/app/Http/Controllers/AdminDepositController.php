<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deposit;

class AdminDepositController extends Controller
{
    public function allocated(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if ($user->role !== 'ADMIN') {
            return response()->json(['success' => false, 'message' => 'Forbidden (admin only).'], 403);
        }

        // Default to PENDING if not provided
        $status = strtoupper((string) $request->query('status', 'PENDING'));

        $items = Deposit::where('storage_type', 'ALLOCATED')
            ->where('status', $status)
            ->orderByDesc('id')
            ->get();

        return response()->json(['success' => true, 'data' => $items]);
    }
}
