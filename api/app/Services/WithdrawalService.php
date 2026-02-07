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


    public function reject(Withdrawal $withdrawal, int $rejectedByUserId, string $reason): Withdrawal
    {
    if ($withdrawal->status !== WithdrawalStatus::PENDING) {
        throw new InvalidArgumentException('Only PENDING withdrawals can be rejected.');
    }

    return DB::transaction(function () use ($withdrawal, $rejectedByUserId, $reason) {
        $withdrawal->status = WithdrawalStatus::REJECTED;
        $withdrawal->rejected_by_user_id = $rejectedByUserId;
        $withdrawal->rejected_at = now();
        $withdrawal->rejection_reason = $reason;
        $withdrawal->save();

        return $withdrawal;
    });
}

public function requestAllocatedByBars(
    Account $account,
    Metal $metal,
    array $barIds,
    int $requestedByUserId
): Withdrawal {
    if ($account->type !== 'INSTITUTIONAL') {
        throw new InvalidArgumentException('Allocated withdrawals require an INSTITUTIONAL account.');
    }

    if (empty($barIds)) {
        throw new InvalidArgumentException('bar_ids is required.');
    }

    return DB::transaction(function () use ($account, $metal, $barIds, $requestedByUserId) {
        $reference = $this->generateReference(); // reuse WD-... format

        // Quantity is derived from bars; store sum on withdrawal for reporting
        $totalKg = '0';

        $bars = \App\Models\Bar::where('account_id', $account->id)
            ->where('metal_id', $metal->id)
            ->whereIn('id', $barIds)
            ->where('status', \App\Enums\BarStatus::AVAILABLE)
            ->lockForUpdate()
            ->get();

        if ($bars->count() !== count($barIds)) {
            throw new InvalidArgumentException('One or more selected bars are invalid or not available.');
        }

        foreach ($bars as $bar) {
            $totalKg = bcadd($totalKg, (string)$bar->weight_kg, 6);
        }

        return Withdrawal::create([
            'account_id' => $account->id,
            'metal_id' => $metal->id,
            'storage_type' => StorageType::ALLOCATED,
            'quantity_kg' => $totalKg,
            'status' => \App\Enums\WithdrawalStatus::PENDING,
            'reference' => $reference,
            'requested_by_user_id' => $requestedByUserId,
            'meta' => ['bar_ids' => $barIds],
        ]);
    });
}

public function approveAllocatedByBars(
    Withdrawal $withdrawal,
    int $approvedByUserId,
    LedgerPostingService $ledger
): Withdrawal {
    if ($withdrawal->status !== \App\Enums\WithdrawalStatus::PENDING) {
        throw new InvalidArgumentException('Only PENDING withdrawals can be approved.');
    }

    if ($withdrawal->storage_type !== \App\Enums\StorageType::ALLOCATED) {
        throw new InvalidArgumentException('Withdrawal is not ALLOCATED.');
    }

    $barIds = $withdrawal->meta['bar_ids'] ?? [];
    if (empty($barIds) || !is_array($barIds)) {
        throw new InvalidArgumentException('Missing bar_ids metadata.');
    }

    return DB::transaction(function () use ($withdrawal, $approvedByUserId, $ledger, $barIds) {
        // Lock bars, validate ownership & availability
        $bars = \App\Models\Bar::where('account_id', $withdrawal->account_id)
            ->where('metal_id', $withdrawal->metal_id)
            ->whereIn('id', $barIds)
            ->where('status', \App\Enums\BarStatus::AVAILABLE)
            ->lockForUpdate()
            ->get();

        if ($bars->count() !== count($barIds)) {
            throw new InvalidArgumentException('One or more bars are no longer available.');
        }

        // Approve
        $withdrawal->status = \App\Enums\WithdrawalStatus::APPROVED;
        $withdrawal->approved_by_user_id = $approvedByUserId;
        $withdrawal->approved_at = now();
        $withdrawal->save();

        // Ledger DEBIT (allocated)
        $ledger->post(
            $withdrawal->account_id,
            $withdrawal->metal_id,
            $withdrawal->storage_type,
            \App\Enums\LedgerDirection::DEBIT,
            'WITHDRAWAL:' . $withdrawal->reference,
            (string) $withdrawal->quantity_kg,
            ['withdrawal_id' => $withdrawal->id, 'bar_ids' => $barIds, 'approved_by' => $approvedByUserId]
        );

        // Mark bars withdrawn
        foreach ($bars as $bar) {
            $bar->status = \App\Enums\BarStatus::WITHDRAWN;
            $bar->withdrawn_at = now();
            $bar->save();
        }

        // Complete
        $withdrawal->status = \App\Enums\WithdrawalStatus::COMPLETED;
        $withdrawal->completed_at = now();
        $withdrawal->save();

        return $withdrawal;
    });
}
}
