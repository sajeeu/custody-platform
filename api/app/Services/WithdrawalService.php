<?php

namespace App\Services;

use App\Enums\LedgerDirection;
use App\Enums\StorageType;
use App\Enums\WithdrawalStatus;
use App\Models\Account;
use App\Models\Metal;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WithdrawalService
{
    public function requestUnallocated(Account $account, Metal $metal, string $quantityKg, int $requestedByUserId): Withdrawal
    {
        if (bccomp($quantityKg, '0', 6) <= 0) {
            throw new InvalidArgumentException('quantity_kg must be greater than 0.');
        }

        return DB::transaction(function () use ($account, $metal, $quantityKg, $requestedByUserId) {
            $reference = $this->generateReference();

            return Withdrawal::create([
                'account_id' => $account->id,
                'metal_id' => $metal->id,
                'storage_type' => StorageType::UNALLOCATED,
                'quantity_kg' => $quantityKg,
                'status' => WithdrawalStatus::PENDING,
                'reference' => $reference,
                'requested_by_user_id' => $requestedByUserId,
                'meta' => null,
            ]);
        });
    }

    public function approveAndComplete(Withdrawal $withdrawal, int $approvedByUserId, LedgerPostingService $ledger): Withdrawal
    {
        if ($withdrawal->status !== WithdrawalStatus::PENDING) {
            throw new InvalidArgumentException('Only PENDING withdrawals can be approved.');
        }

        return DB::transaction(function () use ($withdrawal, $approvedByUserId, $ledger) {
            // Mark approved first (process audit)
            $withdrawal->status = WithdrawalStatus::APPROVED;
            $withdrawal->approved_by_user_id = $approvedByUserId;
            $withdrawal->approved_at = now();
            $withdrawal->save();

            // Post ledger DEBIT (this enforces sufficient balance)
            $ledger->post(
                $withdrawal->account_id,
                $withdrawal->metal_id,
                $withdrawal->storage_type,
                LedgerDirection::DEBIT,
                'WITHDRAWAL:' . $withdrawal->reference,
                (string) $withdrawal->quantity_kg,
                ['withdrawal_id' => $withdrawal->id, 'approved_by' => $approvedByUserId]
            );

            // Mark completed
            $withdrawal->status = WithdrawalStatus::COMPLETED;
            $withdrawal->completed_at = now();
            $withdrawal->save();

            return $withdrawal;
        });
    }

    private function generateReference(): string
    {
        // Simple deterministic format; replace with sequence later if required
        return 'WD-' . now()->format('Ymd-His') . '-' . random_int(100000, 999999);
    }
}
