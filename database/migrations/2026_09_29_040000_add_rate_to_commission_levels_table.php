<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a per-level commission_rate column to commission_levels.
     * Existing rows are backfilled from their parent commission_models.commission_rate
     * so that saved models continue to produce identical results.
     */
    public function up(): void
    {
        Schema::table('commission_levels', function (Blueprint $table) {
            // Nullable so existing rows can be inserted without a rate (fallback to model rate)
            $table->decimal('commission_rate', 8, 4)->nullable()->default(null)->after('level');
        });

        // Backfill existing rows from the parent model's flat rate
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE commission_levels
                SET commission_rate = (
                    SELECT cm.commission_rate
                    FROM commission_models cm
                    WHERE cm.id = commission_levels.commission_model_id
                )
                WHERE commission_rate IS NULL
            ');
        } else {
            DB::statement('
                UPDATE commission_levels cl
                JOIN commission_models cm ON cm.id = cl.commission_model_id
                SET cl.commission_rate = cm.commission_rate
                WHERE cl.commission_rate IS NULL
            ');
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('commission_levels', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }
};
