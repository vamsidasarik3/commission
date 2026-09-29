<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure commission_rate in commission_models is nullable for extensible models
        Schema::table('commission_models', function (Blueprint $table) {
            $table->decimal('commission_rate', 8, 4)->nullable()->default(null)->change();
        });

        // 2. Normalized Model Nodes table (Persons/Representatives/Nodes in the hierarchy)
        Schema::create('model_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_model_id')
                ->constrained('commission_models')
                ->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('model_nodes')
                ->nullOnDelete();
            $table->string('node_type', 50)->default('member');
            $table->decimal('personal_sale', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index(['commission_model_id', 'name']);
            $table->index(['commission_model_id', 'parent_id']);
            $table->index('node_type');
        });

        // 3. Normalized Model Edges / Relationships table (Directed relationships with edge-specific override rates)
        Schema::create('model_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_model_id')
                ->constrained('commission_models')
                ->cascadeOnDelete();
            $table->foreignId('parent_node_id')
                ->constrained('model_nodes')
                ->cascadeOnDelete();
            $table->foreignId('child_node_id')
                ->constrained('model_nodes')
                ->cascadeOnDelete();
            $table->decimal('override_rate', 8, 4)->default(0.0000);
            $table->timestamps();

            $table->unique(['commission_model_id', 'parent_node_id', 'child_node_id'], 'model_edges_unique_edge');
            $table->index('parent_node_id');
            $table->index('child_node_id');
        });

        // 4. Normalized Commission Ledger / Results table (Auditable ledger of each commission calculation)
        Schema::create('commission_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_model_id')
                ->constrained('commission_models')
                ->cascadeOnDelete();
            $table->foreignId('sale_node_id')
                ->constrained('model_nodes')
                ->cascadeOnDelete();
            $table->foreignId('earner_node_id')
                ->constrained('model_nodes')
                ->cascadeOnDelete();
            $table->unsignedInteger('generation');
            $table->decimal('sale_amount', 15, 2)->default(0.00);
            $table->decimal('rate_applied', 8, 4)->default(0.0000);
            $table->decimal('commission_amount', 15, 2)->default(0.00);
            $table->boolean('is_eligible')->default(true);
            $table->timestamps();

            $table->index(['commission_model_id', 'earner_node_id']);
            $table->index(['commission_model_id', 'sale_node_id']);
            $table->index(['commission_model_id', 'generation']);
        });

        // 5. Data backfill: If legacy override tables exist and contain records, backfill into normalized schema
        $this->backfillExistingOverrideData();
    }

    /**
     * Backfill existing Model 2 override records into normalized tables if any exist.
     */
    protected function backfillExistingOverrideData(): void
    {
        if (! Schema::hasTable('override_relationships') || ! Schema::hasTable('override_sales')) {
            return;
        }

        $models = DB::table('commission_models')
            ->where('model_type', 'generation_override')
            ->get();

        foreach ($models as $model) {
            $relationships = DB::table('override_relationships')
                ->where('commission_model_id', $model->id)
                ->get();

            $sales = DB::table('override_sales')
                ->where('commission_model_id', $model->id)
                ->get();

            if ($relationships->isEmpty() && $sales->isEmpty()) {
                continue;
            }

            // Collect all unique node names
            $salesMap = [];
            foreach ($sales as $sale) {
                $salesMap[$sale->salesperson_name] = (float) $sale->amount;
            }

            $allNames = [];
            foreach ($relationships as $rel) {
                $allNames[$rel->parent_name] = true;
                $allNames[$rel->child_name] = true;
            }
            foreach ($sales as $sale) {
                $allNames[$sale->salesperson_name] = true;
            }

            // Create model_nodes
            $nodeIdMap = [];
            foreach (array_keys($allNames) as $name) {
                $personalSale = $salesMap[$name] ?? 0.00;
                $nodeId = DB::table('model_nodes')->insertGetId([
                    'commission_model_id' => $model->id,
                    'name' => $name,
                    'parent_id' => null,
                    'node_type' => 'member',
                    'personal_sale' => $personalSale,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $nodeIdMap[$name] = $nodeId;
            }

            // Update parent_id on nodes and create model_edges
            foreach ($relationships as $rel) {
                $parentId = $nodeIdMap[$rel->parent_name] ?? null;
                $childId = $nodeIdMap[$rel->child_name] ?? null;

                if ($parentId && $childId) {
                    DB::table('model_nodes')
                        ->where('id', $childId)
                        ->update(['parent_id' => $parentId]);

                    DB::table('model_edges')->insert([
                        'commission_model_id' => $model->id,
                        'parent_node_id' => $parentId,
                        'child_node_id' => $childId,
                        'override_rate' => $rel->rate,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Backfill commission ledger from override_commissions if present
            if (Schema::hasTable('override_commissions')) {
                $commissions = DB::table('override_commissions')
                    ->where('commission_model_id', $model->id)
                    ->get();

                foreach ($commissions as $comm) {
                    $saleNodeId = $nodeIdMap[$comm->seller_name] ?? null;
                    $earnerNodeId = $nodeIdMap[$comm->recipient_name] ?? null;

                    if ($saleNodeId && $earnerNodeId) {
                        DB::table('commission_ledger')->insert([
                            'commission_model_id' => $model->id,
                            'sale_node_id' => $saleNodeId,
                            'earner_node_id' => $earnerNodeId,
                            'generation' => $comm->generation,
                            'sale_amount' => $comm->sale_amount,
                            'rate_applied' => $comm->rate,
                            'commission_amount' => $comm->commission_amount,
                            'is_eligible' => (bool) $comm->is_eligible,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_ledger');
        Schema::dropIfExists('model_edges');
        Schema::dropIfExists('model_nodes');

        Schema::table('commission_models', function (Blueprint $table) {
            $table->decimal('commission_rate', 8, 4)->default(0)->change();
        });
    }
};
