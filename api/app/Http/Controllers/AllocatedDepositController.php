<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Metal;
use App\Services\AllocatedDepositService;
use App\Services\LedgerPostingService;
use Illuminate\Http\Request;

class AllocatedDepositController extends Controller
{
    public function create(Request $request, AllocatedDepositService $service, LedgerPostingService $ledger)
    {
        if ($request->user()->role !== 'ADMIN') {
            abort(403, 'Forbidden');
        }

        $data = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'metal_code' => ['required', 'string'],
            'bars' => ['required', 'array', 'min:1'],
            'bars.*.serial' => ['required', 'string'],
            'bars.*.weight_kg' => ['required', 'numeric', 'gt:0'],
            'bars.*.vault' => ['nullable', 'string'],
        ]);

        $account = Account::findOrFail($data['account_id']);
        $metal = Metal::where('code', $data['metal_code'])->firstOrFail();

        $createdBars = $service->registerBarsAndCredit(
            $account,
            $metal,
            $data['bars'],
            $request->user()->id,
            $ledger,
            ['source' => 'ADMIN_ALLOCATED_DEPOSIT']
        );

        return response()->json([
            'success' => true,
            'data' => $createdBars,
        ]);
    }
}
