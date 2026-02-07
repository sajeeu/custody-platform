<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metal_id')->constrained()->cascadeOnDelete();

            $table->string('storage_type')->default('UNALLOCATED'); // for Step 8

            $table->decimal('quantity_kg', 18, 6);

            // PENDING -> APPROVED -> COMPLETED (or REJECTED)
            $table->string('status')->default('PENDING');

            // audit fields
            $table->string('reference')->unique(); // e.g. WD-20260207-000001
            $table->unsignedBigInteger('requested_by_user_id');
            $table->unsignedBigInteger('approved_by_user_id')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['account_id', 'metal_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
