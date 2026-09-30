<?php

namespace Tests\Feature;

use App\Models\CommissionModel;
use Database\Seeders\CommissionModelExamplesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HierarchyDiagramFlowAllModelsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all three commission models display the "Hierarchy Diagram & Commission Flow" section.
     */
    public function test_hierarchy_diagram_and_commission_flow_displays_on_all_models(): void
    {
        $this->seed(CommissionModelExamplesSeeder::class);

        // 1. Model 1 (Weakest Link)
        $m1 = CommissionModel::where('model_type', 'weakest_link')->first();
        $this->assertNotNull($m1);

        $res1 = $this->get(route('commission-models.show', $m1));
        $res1->assertStatus(200);
        $res1->assertSee('Hierarchy Diagram & Commission Flow', false);
        $res1->assertSee('Download Tree (PNG)', false);
        $res1->assertSee('Top Leader Payout', false);

        // 2. Model 2 (Generation Override)
        $m2 = CommissionModel::where('model_type', 'generation_override')->first();
        $this->assertNotNull($m2);

        $res2 = $this->get(route('override-models.show', $m2));
        $res2->assertStatus(200);
        $res2->assertSee('Hierarchy Diagram & Commission Flow', false);
        $res2->assertSee('Download Tree (PNG)', false);
        $res2->assertSee('Total Network Override', false);

        // 3. Model 3 (Unilevel MLM)
        $m3 = CommissionModel::where('model_type', 'unilevel')->first();
        $this->assertNotNull($m3);

        $res3 = $this->get(route('unilevel-models.show', $m3));
        $res3->assertStatus(200);
        $res3->assertSee('Hierarchy Diagram & Commission Flow', false);
        $res3->assertSee('Download Tree (PNG)', false);
        $res3->assertSee('Total Network Commission', false);

        // 4. Model 3 Create page has reference interactive diagram
        $res3Create = $this->get(route('unilevel-models.create'));
        $res3Create->assertStatus(200);
        $res3Create->assertSee('Hierarchy Diagram & Commission Flow', false);
    }
}
