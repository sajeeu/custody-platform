<?php

namespace App\Policies;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepositPolicy
{

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Deposit $deposit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Deposit $deposit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Deposit $deposit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Deposit $deposit): bool
    {
        return false;
    }
    
    public function viewAny(User $user): bool
    {
        return $user->role === 'ADMIN';
    }

    public function view(User $user, Deposit $deposit): bool
    {
        if ($user->role === 'ADMIN') {
            return true;
        }

        return $deposit->account_id === optional($user->account)->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'ADMIN';
    }
}
