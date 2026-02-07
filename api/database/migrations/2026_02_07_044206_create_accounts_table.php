<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type'); // RETAIL | INSTITUTIONAL
            $table->string('status')->default('ACTIVE'); // ACTIVE | SUSPENDED etc

            $table->timestamps();

            $table->unique('user_id'); // enforce exactly 1 account per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
