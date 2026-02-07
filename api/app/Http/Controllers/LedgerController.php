<?php

namespace App\Http\Controllers;

use App\Models\AccountBalance;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function myBalances(Request $request)
    {
        $account = $request->user()->account;

        $balances = AccountBalance::where('account_id', $account->id)
            ->with('metal')
            ->orderBy('metal_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $balances,
        ]);
    }

    public function myLedger(Request $request)
    {
        $account = $request->user()->account;

        $entries = LedgerEntry::where('account_id', $account->id)
            ->with('metal')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $entries,
        ]);
    }
}
