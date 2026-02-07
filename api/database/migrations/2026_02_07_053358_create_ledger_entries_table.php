<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metal_id')->constrained()->cascadeOnDelete();

            // UNALLOCATED now; later we add ALLOCATED and per-vault flows
            $table->string('storage_type')->default('UNALLOCATED');

            // CREDIT or DEBIT
            $table->string('direction');

            // Quantity in kg (custody precision)
            $table->decimal('quantity_kg', 18, 6);

            // Reference for traceability (e.g., DEPOSIT:123, WITHDRAWAL:55)
            $table->string('reference')->index();

            // Optional metadata for audit/debug
            $table->json('meta')->nullable();

            $table->timestamps();

            // Common query pattern: account+metal history
            $table->index(['account_id', 'metal_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
