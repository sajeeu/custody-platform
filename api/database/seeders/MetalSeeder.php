<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Metal;

class MetalSeeder extends Seeder
{
    public function run(): void
    {
        $metals = [
            ['code' => 'GOLD', 'name' => 'Gold'],
            ['code' => 'SILVER', 'name' => 'Silver'],
            ['code' => 'PLATINUM', 'name' => 'Platinum'],
        ];

        foreach ($metals as $m) {
            Metal::updateOrCreate(['code' => $m['code']], $m);
        }
    }
}
