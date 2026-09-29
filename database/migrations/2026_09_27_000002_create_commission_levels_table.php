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
        Schema::create('commission_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_model_id')
                ->constrained('commission_models')
                ->cascadeOnDelete();
            $table->unsignedInteger('level');
            $table->string('main_person');
            $table->decimal('main_sales', 15, 2)->default(0.00);
            $table->decimal('main_commission', 15, 2)->default(0.00);
            $table->string('side_person')->nullable();
            $table->decimal('side_sales', 15, 2)->nullable()->default(0.00);
            $table->decimal('side_commission', 15, 2)->nullable()->default(0.00);
            $table->decimal('selected_commission', 15, 2)->nullable()->default(0.00);
            $table->decimal('leader_commission', 15, 2)->nullable()->default(0.00);
            $table->timestamps();

            $table->index(['commission_model_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_levels');
    }
};
