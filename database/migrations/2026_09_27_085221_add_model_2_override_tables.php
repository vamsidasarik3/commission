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
            $table->string('model_type')->default('weakest_link')->after('name')->index();
            $table->unsignedInteger('max_generations')->default(5)->after('number_of_levels');
            $table->text('description')->nullable()->after('name');
        });

        Schema::create('override_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_model_id')
                ->constrained('commission_models')
                ->cascadeOnDelete();
            $table->string('parent_name');
            $table->string('child_name');
            $table->decimal('rate', 8, 4)->default(0.0000);
            $table->timestamps();

            $table->index(['commission_model_id', 'parent_name']);
            $table->index(['commission_model_id', 'child_name']);
        });

        Schema::create('override_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_model_id')
                ->constrained('commission_models')
                ->cascadeOnDelete();
            $table->string('salesperson_name');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index(['commission_model_id', 'salesperson_name']);
        });

        Schema::create('override_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_model_id')
                ->constrained('commission_models')
                ->cascadeOnDelete();
            $table->string('seller_name');
            $table->decimal('sale_amount', 15, 2)->default(0.00);
            $table->string('recipient_name');
            $table->string('child_name');
            $table->unsignedInteger('generation');
            $table->decimal('rate', 8, 4)->default(0.0000);
            $table->decimal('commission_amount', 15, 2)->default(0.00);
            $table->boolean('is_eligible')->default(true);
            $table->timestamps();

            $table->index(['commission_model_id', 'recipient_name']);
            $table->index(['commission_model_id', 'seller_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('override_commissions');
        Schema::dropIfExists('override_sales');
        Schema::dropIfExists('override_relationships');

        Schema::table('commission_models', function (Blueprint $table) {
            $table->dropColumn(['model_type', 'max_generations', 'description']);
        });
    }
};
