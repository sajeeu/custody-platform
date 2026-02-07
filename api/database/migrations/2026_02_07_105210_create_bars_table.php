<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bars', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metal_id')->constrained()->cascadeOnDelete();

            $table->string('serial')->unique(); // physical serial number
            $table->decimal('weight_kg', 18, 6);

            $table->string('vault')->nullable(); // e.g. MALÉ_VAULT, SINGAPORE_VAULT
            $table->string('status')->default('AVAILABLE'); // AVAILABLE | WITHDRAWN | RESERVED

            $table->unsignedBigInteger('created_by_user_id')->nullable(); // admin/operator
            $table->timestamp('withdrawn_at')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['account_id', 'metal_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bars');
    }
};
