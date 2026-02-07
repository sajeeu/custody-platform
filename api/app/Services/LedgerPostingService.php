<?php

namespace App\Services;

use App\Enums\LedgerDirection;
use App\Enums\StorageType;
use App\Models\AccountBalance;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LedgerPostingService
{
    /**
     * Post a CREDIT/DEBIT entry and update account_balances atomically.
     */
    public function post(
        int $accountId,
        int $metalId,
        string $storageType,
        string $direction,
        string $reference,
        string $quantityKg,
        array $meta = []
    ): LedgerEntry {
        if (!in_array($storageType, StorageType::all(), true)) {
            throw new InvalidArgumentException('Invalid storage type.');
        }

        if (!in_array($direction, LedgerDirection::all(), true)) {
            throw new InvalidArgumentException('Invalid ledger direction.');
        }

        // Prevent zero/negative postings
        if (bccomp($quantityKg, '0', 6) <= 0) {
            throw new InvalidArgumentException('quantity_kg must be greater than 0.');
        }

        return DB::transaction(function () use (
            $accountId, $metalId, $storageType, $direction, $reference, $quantityKg, $meta
        ) {
            // Lock or create the balance row
            $balance = AccountBalance::where('account_id', $accountId)
                ->where('metal_id', $metalId)
                ->where('storage_type', $storageType)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                $balance = AccountBalance::create([
                    'account_id' => $accountId,
                    'metal_id' => $metalId,
                    'storage_type' => $storageType,
                    'balance_kg' => '0',
                ]);

                // Lock it after create to ensure consistent transaction behavior
                $balance = AccountBalance::whereKey($balance->id)->lockForUpdate()->first();
            }

            $newBalance = $balance->balance_kg;

            if ($direction === LedgerDirection::CREDIT) {
                $newBalance = bcadd($newBalance, $quantityKg, 6);
            } else {
                // DEBIT - enforce non-negative
                $newBalance = bcsub($newBalance, $quantityKg, 6);
                if (bccomp($newBalance, '0', 6) < 0) {
                    throw new InvalidArgumentException('Insufficient balance.');
                }
            }

            // Write immutable ledger record
            $entry = LedgerEntry::create([
                'account_id' => $accountId,
                'metal_id' => $metalId,
                'storage_type' => $storageType,
                'direction' => $direction,
                'quantity_kg' => $quantityKg,
                'reference' => $reference,
                'meta' => empty($meta) ? null : $meta,
            ]);

            // Update materialized balance
            $balance->balance_kg = $newBalance;
            $balance->save();

            return $entry;
        });
    }
}
