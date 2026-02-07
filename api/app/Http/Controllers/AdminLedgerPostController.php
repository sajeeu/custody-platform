<?php

namespace App\Http\Controllers;

use App\Enums\StorageType;
use App\Services\LedgerPostingService;
use Illuminate\Http\Request;
use App\Models\Metal;

class AdminLedgerPostController extends Controller
{
    public function post(Request $request, LedgerPostingService $service)
    {
        // Simple role guard (you can refactor to middleware later)
        if ($request->user()->role !== 'ADMIN') {
            abort(403, 'Forbidden');
        }

        $data = $request->validate([
            'account_id' => ['required', 'integer'],
            'metal_code' => ['required', 'string'],
            'direction' => ['required', 'in:CREDIT,DEBIT'],
            'quantity_kg' => ['required', 'numeric', 'gt:0'],
            'reference' => ['required', 'string'],
        ]);

        $metal = Metal::where('code', $data['metal_code'])->firstOrFail();

        $entry = $service->post(
            $data['account_id'],
            $metal->id,
            StorageType::UNALLOCATED,
            $data['direction'],
            $data['reference'],
            number_format((float)$data['quantity_kg'], 6, '.', ''),
            ['posted_by' => $request->user()->id]
        );

        return response()->json([
            'success' => true,
            'data' => $entry,
        ]);
    }
}
