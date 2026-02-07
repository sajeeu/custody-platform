<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function myAccount(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->account,
        ]);
    }

    public function index()
    {
        $this->authorize('viewAny', Account::class);

        return response()->json([
            'success' => true,
            'data' => Account::with('user')->get(),
        ]);
    }

    public function show(Account $account)
    {
        $this->authorize('view', $account);

        return response()->json([
            'success' => true,
            'data' => $account->load('user'),
        ]);
    }
}
