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
        Schema::create('commission_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->decimal('commission_rate', 8, 4)->default(0);
            $table->unsignedInteger('number_of_levels')->default(0);
            $table->decimal('total_sales', 15, 2)->default(0.00);
            $table->decimal('total_potential_commission', 15, 2)->default(0.00);
            $table->decimal('final_commission', 15, 2)->default(0.00);
            $table->string('weakest_person')->nullable();
            $table->decimal('weakest_sales', 15, 2)->nullable();
            $table->decimal('weakest_commission', 15, 2)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_models');
    }
};
