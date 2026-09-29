<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('commission_models', function (Blueprint $table) {
            if (! Schema::hasColumn('commission_models', 'calculation_results')) {
                $table->json('calculation_results')->nullable()->after('final_commission');
            }
        });

        Schema::table('commission_ledger', function (Blueprint $table) {
            if (! Schema::hasColumn('commission_ledger', 'status')) {
                $table->string('status', 20)->default('paid')->after('is_eligible');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_models', function (Blueprint $table) {
            if (Schema::hasColumn('commission_models', 'calculation_results')) {
                $table->dropColumn('calculation_results');
            }
        });

        Schema::table('commission_ledger', function (Blueprint $table) {
            if (Schema::hasColumn('commission_ledger', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
