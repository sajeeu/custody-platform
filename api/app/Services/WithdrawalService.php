<?php

namespace App\Services;

use App\Enums\BarStatus;
use App\Enums\LedgerDirection;
use App\Enums\StorageType;
use App\Enums\WithdrawalStatus;
use App\Models\Account;
use App\Models\Bar;
use App\Models\Metal;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WithdrawalService
{
    /**
     * Create a PENDING unallocated withdrawal request (no ledger movement yet).
     */
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

    /**
     * Approve a PENDING unallocated withdrawal:
     * - marks approved
     * - posts ledger DEBIT (enforces sufficient balance)
     * - marks completed
     */
    public function approveAndComplete(Withdrawal $withdrawal, int $approvedByUserId, LedgerPostingService $ledger): Withdrawal
    {
        if ($withdrawal->status !== WithdrawalStatus::PENDING) {
            throw new InvalidArgumentException('Only PENDING withdrawals can be approved.');
        }

        if ($withdrawal->storage_type !== StorageType::UNALLOCATED) {
            throw new InvalidArgumentException('Withdrawal is not UNALLOCATED.');
        }

        return DB::transaction(function () use ($withdrawal, $approvedByUserId, $ledger) {
            // Approve (process audit)
            $withdrawal->status = WithdrawalStatus::APPROVED;
            $withdrawal->approved_by_user_id = $approvedByUserId;
            $withdrawal->approved_at = now();
            $withdrawal->save();

            // Ledger DEBIT (unallocated)
            $ledger->post(
                $withdrawal->account_id,
                $withdrawal->metal_id,
                $withdrawal->storage_type,
                LedgerDirection::DEBIT,
                'WITHDRAWAL:' . $withdrawal->reference,
                (string) $withdrawal->quantity_kg,
                ['withdrawal_id' => $withdrawal->id, 'approved_by' => $approvedByUserId]
            );

            // Complete
            $withdrawal->status = WithdrawalStatus::COMPLETED;
            $withdrawal->completed_at = now();
            $withdrawal->save();

            return $withdrawal;
        });
    }

    /**
     * Create a PENDING allocated withdrawal by selecting bar IDs:
     * - validates institutional account
     * - locks & verifies bars are AVAILABLE
     * - creates withdrawal with quantity derived from bar weights
     * - reserves bars (AVAILABLE -> RESERVED) linked to withdrawal
     */
    public function requestAllocatedByBars(Account $account, Metal $metal, array $barIds, int $requestedByUserId): Withdrawal
    {
        if ($account->type !== 'INSTITUTIONAL') {
            throw new InvalidArgumentException('Allocated withdrawals require an INSTITUTIONAL account.');
        }

        if (empty($barIds)) {
            throw new InvalidArgumentException('bar_ids is required.');
        }

        // Normalize bar IDs to ints (safe for comparisons)
        $barIds = array_values(array_unique(array_map('intval', $barIds)));

        return DB::transaction(function () use ($account, $metal, $barIds, $requestedByUserId) {
            $reference = $this->generateReference();

            $bars = Bar::where('account_id', $account->id)
                ->where('metal_id', $metal->id)
                ->whereIn('id', $barIds)
                ->where('status', BarStatus::AVAILABLE)
                ->lockForUpdate()
                ->get();

            if ($bars->count() !== count($barIds)) {
                throw new InvalidArgumentException('One or more selected bars are invalid or not available.');
            }

            $totalKg = '0';
            foreach ($bars as $bar) {
                $totalKg = bcadd($totalKg, (string) $bar->weight_kg, 6);
            }

            $withdrawal = Withdrawal::create([
                'account_id' => $account->id,
                'metal_id' => $metal->id,
                'storage_type' => StorageType::ALLOCATED,
                'quantity_kg' => $totalKg,
                'status' => WithdrawalStatus::PENDING,
                'reference' => $reference,
                'requested_by_user_id' => $requestedByUserId,
                'meta' => ['bar_ids' => $barIds],
            ]);

            // Reserve bars against this withdrawal
            foreach ($bars as $bar) {
                $bar->status = BarStatus::RESERVED;
                $bar->reserved_by_withdrawal_id = $withdrawal->id;
                $bar->reserved_at = now();
                $bar->save();
            }

            return $withdrawal;
        });
    }

    /**
     * Approve a PENDING allocated withdrawal:
     * - locks & verifies bars are RESERVED by this withdrawal
     * - marks approved
     * - posts ledger DEBIT for total
     * - marks bars WITHDRAWN and clears reservation linkage
     * - marks withdrawal COMPLETED
     */
    public function approveAllocatedByBars(Withdrawal $withdrawal, int $approvedByUserId, LedgerPostingService $ledger): Withdrawal
    {
        if ($withdrawal->status !== WithdrawalStatus::PENDING) {
            throw new InvalidArgumentException('Only PENDING withdrawals can be approved.');
        }

        if ($withdrawal->storage_type !== StorageType::ALLOCATED) {
            throw new InvalidArgumentException('Withdrawal is not ALLOCATED.');
        }

        $barIds = $withdrawal->meta['bar_ids'] ?? [];
        if (!is_array($barIds) || empty($barIds)) {
            throw new InvalidArgumentException('Missing bar_ids metadata.');
        }

        $barIds = array_values(array_unique(array_map('intval', $barIds)));

        return DB::transaction(function () use ($withdrawal, $approvedByUserId, $ledger, $barIds) {
            $bars = Bar::where('account_id', $withdrawal->account_id)
                ->where('metal_id', $withdrawal->metal_id)
                ->whereIn('id', $barIds)
                ->where('status', BarStatus::RESERVED)
                ->where('reserved_by_withdrawal_id', $withdrawal->id)
                ->lockForUpdate()
                ->get();

            if ($bars->count() !== count($barIds)) {
                throw new InvalidArgumentException('One or more bars are no longer reserved for this withdrawal.');
            }

            // Approve
            $withdrawal->status = WithdrawalStatus::APPROVED;
            $withdrawal->approved_by_user_id = $approvedByUserId;
            $withdrawal->approved_at = now();
            $withdrawal->save();

            // Ledger DEBIT (allocated)
            $ledger->post(
                $withdrawal->account_id,
                $withdrawal->metal_id,
                $withdrawal->storage_type,
                LedgerDirection::DEBIT,
                'WITHDRAWAL:' . $withdrawal->reference,
                (string) $withdrawal->quantity_kg,
                [
                    'withdrawal_id' => $withdrawal->id,
                    'bar_ids' => $barIds,
                    'approved_by' => $approvedByUserId,
                ]
            );

            // Mark bars withdrawn and clear reservation fields
            foreach ($bars as $bar) {
                $bar->status = BarStatus::WITHDRAWN;
                $bar->withdrawn_at = now();
                $bar->reserved_by_withdrawal_id = null;
                $bar->reserved_at = null;
                $bar->save();
            }

            // Complete
            $withdrawal->status = WithdrawalStatus::COMPLETED;
            $withdrawal->completed_at = now();
            $withdrawal->save();

            return $withdrawal;
        });
    }

    /**
     * Reject a withdrawal:
     * - For ALLOCATED withdrawals: release reserved bars back to AVAILABLE.
     * - Idempotent: if already REJECTED, still attempts to release bars if they are stuck.
     * - No ledger movement.
     */
    public function reject(Withdrawal $withdrawal, int $rejectedByUserId, string $reason): Withdrawal
    {
        if (!in_array($withdrawal->status, [WithdrawalStatus::PENDING, WithdrawalStatus::REJECTED], true)) {
            throw new InvalidArgumentException('Only PENDING/REJECTED withdrawals can be rejected/re-processed.');
        }

        return DB::transaction(function () use ($withdrawal, $rejectedByUserId, $reason) {

            if ($withdrawal->storage_type === StorageType::ALLOCATED) {
                $barIds = $withdrawal->meta['bar_ids'] ?? [];

                if (is_array($barIds) && !empty($barIds)) {
                    $barIds = array_values(array_unique(array_map('intval', $barIds)));

                    $bars = Bar::where('account_id', $withdrawal->account_id)
                        ->where('metal_id', $withdrawal->metal_id)
                        ->whereIn('id', $barIds)
                        ->where('status', BarStatus::RESERVED)
                        ->where('reserved_by_withdrawal_id', $withdrawal->id)
                        ->lockForUpdate()
                        ->get();

                    foreach ($bars as $bar) {
                        $bar->status = BarStatus::AVAILABLE;
                        $bar->reserved_by_withdrawal_id = null;
                        $bar->reserved_at = null;
                        $bar->save();
                    }
                }
            }

            $withdrawal->status = WithdrawalStatus::REJECTED;
            $withdrawal->rejected_by_user_id = $rejectedByUserId;
            $withdrawal->rejected_at = now();
            $withdrawal->rejection_reason = $reason;
            $withdrawal->save();

            return $withdrawal;
        });
    }

    private function generateReference(): string
    {
        return 'WD-' . now()->format('Ymd-His') . '-' . random_int(100000, 999999);
    }
}
