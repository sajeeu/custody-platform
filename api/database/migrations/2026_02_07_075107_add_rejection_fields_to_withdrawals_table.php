<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('rejected_by_user_id')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'rejected_by_user_id', 'rejected_at']);
            $table->dropIndex(['status', 'created_at']);
        });
    }
};
