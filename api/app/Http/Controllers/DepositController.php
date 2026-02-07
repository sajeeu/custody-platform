<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Deposit;
use App\Models\Metal;
use App\Services\DepositService;
use App\Services\LedgerPostingService;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    // Admin: create & complete deposit (credits ledger)
    public function create(Request $request, DepositService $service, LedgerPostingService $ledger)
    {
        $this->authorize('create', Deposit::class);

        $data = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'metal_code' => ['required', 'string'],
            'quantity_kg' => ['required', 'numeric', 'gt:0'],
        ]);

        $account = Account::findOrFail($data['account_id']);
        $metal = Metal::where('code', $data['metal_code'])->firstOrFail();

        $deposit = $service->createAndCompleteUnallocated(
            $account,
            $metal,
            number_format((float)$data['quantity_kg'], 6, '.', ''),
            $request->user()->id,
            $ledger,
            ['source' => 'ADMIN_POST']
        );

        return response()->json([
            'success' => true,
            'data' => $deposit->load('metal'),
        ]);
    }

    // User: my deposit history
    public function myDeposits(Request $request)
    {
        $account = $request->user()->account;

        $deposits = Deposit::where('account_id', $account->id)
            ->with('metal')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $deposits,
        ]);
    }

    // Admin: deposits list (queue/history)
    public function adminList(Request $request)
    {
        $this->authorize('viewAny', Deposit::class);

        $status = $request->query('status'); // optional

        $deposits = Deposit::with(['metal', 'account'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $deposits,
        ]);
    }
}
