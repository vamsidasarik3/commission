<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Model1PresetCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the Progressive Ladder preset live calculation and storage.
     */
    public function test_progressive_ladder_preset_can_calculate_and_save(): void
    {
        $progressiveData = [
            'model_type' => 'weakest_link',
            'name' => 'Model 1: Progressive Escalation Ladder',
            'commission_rate' => 5.0,
            'number_of_levels' => 10,
            'levels' => [
                ['level' => 1, 'commission_rate' => 3.0, 'main_person' => 'B', 'main_sales' => 0.0, 'side_person' => 'S1', 'side_sales' => 2000.0],
                ['level' => 2, 'commission_rate' => 4.0, 'main_person' => 'C', 'main_sales' => 0.0, 'side_person' => 'S2', 'side_sales' => 1800.0],
                ['level' => 3, 'commission_rate' => 5.0, 'main_person' => 'D', 'main_sales' => 0.0, 'side_person' => 'S3', 'side_sales' => 1500.0],
                ['level' => 4, 'commission_rate' => 6.0, 'main_person' => 'E', 'main_sales' => 0.0, 'side_person' => 'S4', 'side_sales' => 1400.0],
                ['level' => 5, 'commission_rate' => 7.0, 'main_person' => 'F', 'main_sales' => 0.0, 'side_person' => 'S5', 'side_sales' => 1200.0],
                ['level' => 6, 'commission_rate' => 8.0, 'main_person' => 'G', 'main_sales' => 0.0, 'side_person' => 'S6', 'side_sales' => 1100.0],
                ['level' => 7, 'commission_rate' => 9.0, 'main_person' => 'H', 'main_sales' => 0.0, 'side_person' => 'S7', 'side_sales' => 1000.0],
                ['level' => 8, 'commission_rate' => 10.0, 'main_person' => 'I', 'main_sales' => 0.0, 'side_person' => 'S8', 'side_sales' => 900.0],
                ['level' => 9, 'commission_rate' => 12.0, 'main_person' => 'J', 'main_sales' => 0.0, 'side_person' => 'S9', 'side_sales' => 800.0],
                ['level' => 10, 'commission_rate' => 15.0, 'main_person' => 'K', 'main_sales' => 300.0, 'side_person' => 'S10', 'side_sales' => 700.0],
            ],
        ];

        // 1. Verify calculation endpoint returns 200 with calculation data
        $calcResponse = $this->postJson(route('commission-models.calculate'), $progressiveData);
        $calcResponse->assertStatus(200);
        $calcResponse->assertJson(['success' => true]);
        $calcResponse->assertJsonPath('data.number_of_levels', 10);
        $calcResponse->assertJsonPath('data.weakest_person', 'K');

        // 2. Verify store endpoint saves successfully
        $saveResponse = $this->post(route('commission-models.store'), $progressiveData);
        $saveResponse->assertRedirect();

        $this->assertDatabaseHas('commission_models', [
            'name' => 'Model 1: Progressive Escalation Ladder',
            'model_type' => 'weakest_link',
            'number_of_levels' => 10,
            'weakest_person' => 'K',
        ]);
    }

    /**
     * Test the Margin Decay preset live calculation and storage.
     */
    public function test_margin_decay_preset_can_calculate_and_save(): void
    {
        $regressiveData = [
            'model_type' => 'weakest_link',
            'name' => 'Model 1: Regressive Margin Decay Plan',
            'commission_rate' => 8.0,
            'number_of_levels' => 10,
            'levels' => [
                ['level' => 1, 'commission_rate' => 15.0, 'main_person' => 'B', 'main_sales' => 0.0, 'side_person' => 'S1', 'side_sales' => 500.0],
                ['level' => 2, 'commission_rate' => 12.5, 'main_person' => 'C', 'main_sales' => 0.0, 'side_person' => 'S2', 'side_sales' => 600.0],
                ['level' => 3, 'commission_rate' => 10.0, 'main_person' => 'D', 'main_sales' => 0.0, 'side_person' => 'S3', 'side_sales' => 800.0],
                ['level' => 4, 'commission_rate' => 8.5, 'main_person' => 'E', 'main_sales' => 0.0, 'side_person' => 'S4', 'side_sales' => 900.0],
                ['level' => 5, 'commission_rate' => 7.0, 'main_person' => 'F', 'main_sales' => 0.0, 'side_person' => 'S5', 'side_sales' => 1000.0],
                ['level' => 6, 'commission_rate' => 6.0, 'main_person' => 'G', 'main_sales' => 0.0, 'side_person' => 'S6', 'side_sales' => 1200.0],
                ['level' => 7, 'commission_rate' => 5.0, 'main_person' => 'H', 'main_sales' => 0.0, 'side_person' => 'S7', 'side_sales' => 1400.0],
                ['level' => 8, 'commission_rate' => 4.0, 'main_person' => 'I', 'main_sales' => 0.0, 'side_person' => 'S8', 'side_sales' => 1600.0],
                ['level' => 9, 'commission_rate' => 3.0, 'main_person' => 'J', 'main_sales' => 0.0, 'side_person' => 'S9', 'side_sales' => 1000.0],
                ['level' => 10, 'commission_rate' => 2.5, 'main_person' => 'K', 'main_sales' => 2500.0, 'side_person' => 'S10', 'side_sales' => 2000.0],
            ],
        ];

        $calcResponse = $this->postJson(route('commission-models.calculate'), $regressiveData);
        $calcResponse->assertStatus(200);
        $calcResponse->assertJson(['success' => true]);

        $saveResponse = $this->post(route('commission-models.store'), $regressiveData);
        $saveResponse->assertRedirect();

        $this->assertDatabaseHas('commission_models', [
            'name' => 'Model 1: Regressive Margin Decay Plan',
            'model_type' => 'weakest_link',
            'number_of_levels' => 10,
        ]);
    }

    /**
     * Test the Dynamic Non-Fixed Rates preset live calculation and storage.
     */
    public function test_dynamic_non_fixed_rates_preset_can_calculate_and_save(): void
    {
        $dynamicData = [
            'model_type' => 'weakest_link',
            'name' => 'Model 1: Dynamic Rep-Specific Rates',
            'commission_rate' => 7.0,
            'number_of_levels' => 10,
            'levels' => [
                ['level' => 1, 'commission_rate' => 5.5, 'main_person' => 'B', 'main_sales' => 0.0, 'side_person' => 'S1', 'side_sales' => 1500.0],
                ['level' => 2, 'commission_rate' => 8.0, 'main_person' => 'C', 'main_sales' => 0.0, 'side_person' => 'S2', 'side_sales' => 1200.0],
                ['level' => 3, 'commission_rate' => 4.5, 'main_person' => 'D', 'main_sales' => 0.0, 'side_person' => 'S3', 'side_sales' => 2000.0],
                ['level' => 4, 'commission_rate' => 11.0, 'main_person' => 'E', 'main_sales' => 0.0, 'side_person' => 'S4', 'side_sales' => 800.0],
                ['level' => 5, 'commission_rate' => 6.5, 'main_person' => 'F', 'main_sales' => 0.0, 'side_person' => 'S5', 'side_sales' => 1400.0],
                ['level' => 6, 'commission_rate' => 9.5, 'main_person' => 'G', 'main_sales' => 0.0, 'side_person' => 'S6', 'side_sales' => 1000.0],
                ['level' => 7, 'commission_rate' => 3.5, 'main_person' => 'H', 'main_sales' => 0.0, 'side_person' => 'S7', 'side_sales' => 2500.0],
                ['level' => 8, 'commission_rate' => 12.0, 'main_person' => 'I', 'main_sales' => 0.0, 'side_person' => 'S8', 'side_sales' => 750.0],
                ['level' => 9, 'commission_rate' => 7.5, 'main_person' => 'J', 'main_sales' => 0.0, 'side_person' => 'S9', 'side_sales' => 1100.0],
                ['level' => 10, 'commission_rate' => 10.0, 'main_person' => 'K', 'main_sales' => 500.0, 'side_person' => 'S10', 'side_sales' => 900.0],
            ],
        ];

        $calcResponse = $this->postJson(route('commission-models.calculate'), $dynamicData);
        $calcResponse->assertStatus(200);
        $calcResponse->assertJson(['success' => true]);

        $saveResponse = $this->post(route('commission-models.store'), $dynamicData);
        $saveResponse->assertRedirect();

        $this->assertDatabaseHas('commission_models', [
            'name' => 'Model 1: Dynamic Rep-Specific Rates',
            'model_type' => 'weakest_link',
            'number_of_levels' => 10,
        ]);
    }
}
