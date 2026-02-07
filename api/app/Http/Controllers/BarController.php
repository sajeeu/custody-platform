<?php

namespace App\Http\Controllers;

use App\Enums\BarStatus;
use App\Models\Bar;
use Illuminate\Http\Request;

class BarController extends Controller
{
    public function myBars(Request $request)
    {
        $account = $request->user()->account;

        $bars = Bar::where('account_id', $account->id)
            ->with('metal')
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bars,
        ]);
    }

    public function myAvailableBars(Request $request)
    {
        $account = $request->user()->account;

        $bars = Bar::where('account_id', $account->id)
            ->where('status', BarStatus::AVAILABLE)
            ->with('metal')
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bars,
        ]);
    }

    public function adminList(Request $request)
    {
        $this->authorize('viewAny', Bar::class);

        $status = $request->query('status');

        $bars = Bar::with(['metal', 'account'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bars,
        ]);
    }
}
