<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metal_id')->constrained()->cascadeOnDelete();

            $table->string('storage_type')->default('UNALLOCATED');

            $table->decimal('quantity_kg', 18, 6);

            // PENDING -> COMPLETED (or REJECTED) if you want later
            $table->string('status')->default('PENDING');

            $table->string('reference')->unique(); // e.g. DP-20260207-000001

            $table->unsignedBigInteger('created_by_user_id')->nullable(); // admin/operator
            $table->timestamp('completed_at')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['account_id', 'metal_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
