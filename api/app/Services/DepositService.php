<?php

namespace App\Services;

use App\Enums\DepositStatus;
use App\Enums\LedgerDirection;
use App\Enums\StorageType;
use App\Models\Account;
use App\Models\Deposit;
use App\Models\Metal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DepositService
{
    public function createAndCompleteUnallocated(
        Account $account,
        Metal $metal,
        string $quantityKg,
        int $createdByUserId,
        LedgerPostingService $ledger,
        array $meta = []
    ): Deposit {
        if (bccomp($quantityKg, '0', 6) <= 0) {
            throw new InvalidArgumentException('quantity_kg must be greater than 0.');
        }

        return DB::transaction(function () use ($account, $metal, $quantityKg, $createdByUserId, $ledger, $meta) {
            $reference = $this->generateReference();

            $deposit = Deposit::create([
                'account_id' => $account->id,
                'metal_id' => $metal->id,
                'storage_type' => StorageType::UNALLOCATED,
                'quantity_kg' => $quantityKg,
                'status' => DepositStatus::PENDING,
                'reference' => $reference,
                'created_by_user_id' => $createdByUserId,
                'meta' => empty($meta) ? null : $meta,
            ]);

            // Post ledger CREDIT
            $ledger->post(
                $deposit->account_id,
                $deposit->metal_id,
                $deposit->storage_type,
                LedgerDirection::CREDIT,
                'DEPOSIT:' . $deposit->reference,
                (string) $deposit->quantity_kg,
                ['deposit_id' => $deposit->id, 'created_by' => $createdByUserId]
            );

            $deposit->status = DepositStatus::COMPLETED;
            $deposit->completed_at = now();
            $deposit->save();

            return $deposit;
        });
    }

    private function generateReference(): string
    {
        return 'DP-' . now()->format('Ymd-His') . '-' . random_int(100000, 999999);
    }
}
