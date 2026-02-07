<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

class WithdrawalPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin can view all withdrawals (queue)
        return $user->role === 'ADMIN';
    }

    public function view(User $user, Withdrawal $withdrawal): bool
    {
        // Admin sees all; users see only their own withdrawals
        if ($user->role === 'ADMIN') {
            return true;
        }

        return $withdrawal->account_id === optional($user->account)->id;
    }

    public function reject(User $user, Withdrawal $withdrawal): bool
    {
        // Only admin can reject, only if PENDING
        return $user->role === 'ADMIN' && $withdrawal->status === 'PENDING';
    }
}
