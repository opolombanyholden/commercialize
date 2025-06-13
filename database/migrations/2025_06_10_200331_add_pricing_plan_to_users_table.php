<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('pricing_plan_id')->nullable()->constrained('pricing_plans')->after('email');
            $table->decimal('account_balance', 10, 2)->default(0)->after('pricing_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pricing_plan_id']);
            $table->dropColumn(['pricing_plan_id', 'account_balance']);
        });
    }
};