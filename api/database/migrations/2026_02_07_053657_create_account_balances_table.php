<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_balances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metal_id')->constrained()->cascadeOnDelete();

            $table->string('storage_type')->default('UNALLOCATED');

            // Current balance in kg
            $table->decimal('balance_kg', 18, 6)->default(0);

            $table->timestamps();

            // Enforce one row per (account, metal, storage_type)
            $table->unique(['account_id', 'metal_id', 'storage_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_balances');
    }
};
