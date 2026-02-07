<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bars', function (Blueprint $table) {
            $table->unsignedBigInteger('reserved_by_withdrawal_id')->nullable()->after('status');
            $table->timestamp('reserved_at')->nullable()->after('reserved_by_withdrawal_id');

            $table->index(['status', 'reserved_by_withdrawal_id']);
        });
    }

    public function down(): void
    {
        Schema::table('bars', function (Blueprint $table) {
            $table->dropIndex(['status', 'reserved_by_withdrawal_id']);
            $table->dropColumn(['reserved_by_withdrawal_id', 'reserved_at']);
        });
    }
};
