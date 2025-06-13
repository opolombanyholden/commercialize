<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('protected_pdfs', function (Blueprint $table) {
            $table->string('security_hash')->nullable()->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('protected_pdfs', function (Blueprint $table) {
            $table->dropColumn('security_hash');
        });
    }
};