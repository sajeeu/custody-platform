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
    public function approve(Request $request, \App\Models\Withdrawal $withdrawal, \App\Services\WithdrawalService $service, \App\Services\LedgerPostingService $ledger)
{
    if ($request->user()->role !== 'ADMIN') {
        abort(403, 'Forbidden');
    }

    if ($withdrawal->storage_type === \App\Enums\StorageType::ALLOCATED) {
        $updated = $service->approveAllocatedByBars($withdrawal, $request->user()->id, $ledger);
    } else {
        $updated = $service->approveAndComplete($withdrawal, $request->user()->id, $ledger);
    }

    return response()->json([
        'success' => true,
        'data' => $updated,
    ]);
}



    public function myWithdrawals(Request $request)
    {
        $account = $request->user()->account;

        $withdrawals = Withdrawal::where('account_id', $account->id)
            ->with('metal')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $withdrawals,
        ]);
    }

    public function adminQueue(Request $request)
    {
        $this->authorize('viewAny', Withdrawal::class);

        $status = $request->query('status', 'PENDING');

        $withdrawals = Withdrawal::with(['metal', 'account'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('created_at')
            ->limit(200)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $withdrawals,
        ]);
    }

    public function reject(Request $request, Withdrawal $withdrawal, WithdrawalService $service)
    {
        $this->authorize('reject', $withdrawal);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $updated = $service->reject($withdrawal, $request->user()->id, $data['reason']);

        return response()->json([
            'success' => true,
            'data' => $updated,
        ]);
}

public function requestAllocated(Request $request, \App\Services\WithdrawalService $service)
{
    $data = $request->validate([
        'metal_code' => ['required', 'string'],
        'bar_ids' => ['required', 'array', 'min:1'],
        'bar_ids.*' => ['integer'],
    ]);

    $metal = \App\Models\Metal::where('code', $data['metal_code'])->firstOrFail();
    $account = $request->user()->account;

    $withdrawal = $service->requestAllocatedByBars(
        $account,
        $metal,
        $data['bar_ids'],
        $request->user()->id
    );

    return response()->json([
        'success' => true,
        'data' => $withdrawal,
    ]);
}

}
