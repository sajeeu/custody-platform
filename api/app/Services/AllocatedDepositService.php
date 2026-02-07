<?php

namespace App\Services;

use App\Enums\BarStatus;
use App\Enums\LedgerDirection;
use App\Enums\StorageType;
use App\Models\Account;
use App\Models\Bar;
use App\Models\Metal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AllocatedDepositService
{
    public function registerBarsAndCredit(
        Account $account,
        Metal $metal,
        array $bars,
        int $createdByUserId,
        LedgerPostingService $ledger,
        array $meta = []
    ): array {
        if ($account->type !== 'INSTITUTIONAL') {
            throw new InvalidArgumentException('Allocated deposits require an INSTITUTIONAL account.');
        }

        if (empty($bars)) {
            throw new InvalidArgumentException('bars array is required.');
        }

        return DB::transaction(function () use ($account, $metal, $bars, $createdByUserId, $ledger, $meta) {
            $createdBars = [];
            $totalKg = '0';

            foreach ($bars as $b) {
                if (empty($b['serial']) || empty($b['weight_kg'])) {
                    throw new InvalidArgumentException('Each bar must include serial and weight_kg.');
                }

                $weight = number_format((float)$b['weight_kg'], 6, '.', '');
                if (bccomp($weight, '0', 6) <= 0) {
                    throw new InvalidArgumentException('Bar weight_kg must be > 0.');
                }

                $bar = Bar::create([
                    'account_id' => $account->id,
                    'metal_id' => $metal->id,
                    'serial' => (string) $b['serial'],
                    'weight_kg' => $weight,
                    'vault' => $b['vault'] ?? null,
                    'status' => BarStatus::AVAILABLE,
                    'created_by_user_id' => $createdByUserId,
                    'meta' => empty($meta) ? null : $meta,
                ]);

                $createdBars[] = $bar;
                $totalKg = bcadd($totalKg, $weight, 6);
            }

            // Ledger CREDIT for allocated holdings (sum of bars)
            $ledger->post(
                $account->id,
                $metal->id,
                StorageType::ALLOCATED,
                LedgerDirection::CREDIT,
                'ALLOCATED_DEPOSIT:' . now()->format('Ymd-His') . '-' . random_int(100000, 999999),
                $totalKg,
                ['bar_ids' => collect($createdBars)->pluck('id')->all(), 'created_by' => $createdByUserId]
            );

            return $createdBars;
        });
    }
}
