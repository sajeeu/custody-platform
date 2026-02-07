<?php

namespace App\Http\Controllers;

use App\Models\Metal;
use App\Models\Withdrawal;
use App\Services\LedgerPostingService;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    // User requests withdrawal (UNALLOCATED)
    public function request(Request $request, WithdrawalService $service)
    {
        $data = $request->validate([
            'metal_code' => ['required', 'string'],
            'quantity_kg' => ['required', 'numeric', 'gt:0'],
        ]);

        $metal = Metal::where('code', $data['metal_code'])->firstOrFail();
        $account = $request->user()->account;

        $withdrawal = $service->requestUnallocated(
            $account,
            $metal,
            number_format((float)$data['quantity_kg'], 6, '.', ''),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'data' => $withdrawal,
        ]);
    }

    // Admin approves & completes (posts ledger debit)
    public function approve(Request $request, Withdrawal $withdrawal, WithdrawalService $service, LedgerPostingService $ledger)
    {
        if ($request->user()->role !== 'ADMIN') {
            abort(403, 'Forbidden');
        }

        $updated = $service->approveAndComplete($withdrawal, $request->user()->id, $ledger);

        return response()->json([
            'success' => true,
            'data' => $updated,
        ]);
    }
}
