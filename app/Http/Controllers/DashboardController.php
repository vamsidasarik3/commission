<?php

namespace App\Http\Controllers;

use App\Models\CommissionModel;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Commission Model dashboard with support for Model 1 and Model 2.
     */
    public function index(): View
    {
        $totalCount = CommissionModel::count();
        $model1Count = CommissionModel::where('model_type', 'weakest_link')->orWhereNull('model_type')->count();
        $model2Count = CommissionModel::where('model_type', 'generation_override')->count();
        $model3Count = CommissionModel::where('model_type', 'unilevel')->count();
        $totalSalesSum = CommissionModel::sum('total_sales');

        $statistics = [
            [
                'title' => 'Total Commission Models',
                'value' => (string) $totalCount,
                'change' => "{$model1Count} M1 • {$model2Count} M2 • {$model3Count} M3",
                'trend' => 'up',
                'icon' => 'models',
                'badge' => 'Active',
            ],
            [
                'title' => 'Model 1 (Weakest Link)',
                'value' => (string) $model1Count,
                'change' => 'Bottleneck MIN engine',
                'trend' => 'neutral',
                'icon' => 'bottleneck',
                'badge' => 'Model 1',
            ],
            [
                'title' => 'Model 2 (Override)',
                'value' => (string) $model2Count,
                'change' => 'Multi-upline overrides',
                'trend' => 'up',
                'icon' => 'tiers',
                'badge' => 'Model 2',
            ],
            [
                'title' => 'Model 3 (Unilevel MLM)',
                'value' => (string) $model3Count,
                'change' => '10-Gen MLM schedule',
                'trend' => 'up',
                'icon' => 'teams',
                'badge' => 'Model 3',
            ],
        ];

        $recentModels = [
            [
                'id' => 1,
                'name' => 'Q3 Enterprise Core Model',
                'code' => 'WLM-ENT-2026',
                'weakest_link_kpi' => 'Lowest Team Quota Attainment',
                'threshold' => '65% minimum floor',
                'status' => 'Active',
                'updated_at' => '2 hours ago',
                'created_by' => 'Finance Ops',
            ],
            [
                'id' => 2,
                'name' => 'Inside Sales Mid-Market Pool',
                'code' => 'WLM-MM-0926',
                'weakest_link_kpi' => 'Minimum Deal Close Ratio',
                'threshold' => '18% stage-to-close',
                'status' => 'Active',
                'updated_at' => 'Yesterday',
                'created_by' => 'RevOps',
            ],
            [
                'id' => 3,
                'name' => 'Channel Partner Shared Margin',
                'code' => 'WLM-PART-01',
                'weakest_link_kpi' => 'Partner Customer Retention',
                'threshold' => '85% NRR baseline',
                'status' => 'Draft',
                'updated_at' => '3 days ago',
                'created_by' => 'Channel Lead',
            ],
            [
                'id' => 4,
                'name' => 'Strategic Accounts Pod Matrix',
                'code' => 'WLM-STRAT-V2',
                'weakest_link_kpi' => 'Lowest Performing SDR Metric',
                'threshold' => '12 qualified demos',
                'status' => 'Under Review',
                'updated_at' => 'Sep 24, 2026',
                'created_by' => 'VP Sales',
            ],
        ];

        $savedModels = [
            [
                'id' => 1,
                'name' => 'Q3 Enterprise Core Model',
                'category' => 'Enterprise Direct',
                'bottleneck_rule' => 'Lowest Team Attainment governs pool multiplier',
                'tier_count' => 5,
                'base_commission' => '8.5%',
                'floor_penalty' => '-25% under 70%',
                'status' => 'Active',
                'last_run' => 'Sep 26, 2026',
            ],
            [
                'id' => 2,
                'name' => 'Inside Sales Mid-Market Pool',
                'category' => 'Mid-Market Velocity',
                'bottleneck_rule' => 'Weakest SDR conversion limits AE payout tier',
                'tier_count' => 4,
                'base_commission' => '10.0%',
                'floor_penalty' => '-15% under benchmark',
                'status' => 'Active',
                'last_run' => 'Sep 25, 2026',
            ],
            [
                'id' => 3,
                'name' => 'Channel Partner Shared Margin',
                'category' => 'Channel & Resellers',
                'bottleneck_rule' => 'Customer satisfaction floor overrides tier scale',
                'tier_count' => 3,
                'base_commission' => '6.0%',
                'floor_penalty' => 'Cap at Tier 1 if CSAT < 4.0',
                'status' => 'Draft',
                'last_run' => 'Never',
            ],
            [
                'id' => 4,
                'name' => 'Strategic Accounts Pod Matrix',
                'category' => 'Key Accounts',
                'bottleneck_rule' => 'Pod-wide bonus anchored to minimum rep quota %',
                'tier_count' => 6,
                'base_commission' => '12.0%',
                'floor_penalty' => 'No multiplier if any rep < 50%',
                'status' => 'Under Review',
                'last_run' => 'Sep 22, 2026',
            ],
            [
                'id' => 5,
                'name' => 'Cross-Sell Synergy Baseline',
                'category' => 'Expansion & Renewals',
                'bottleneck_rule' => 'Renewal rate floor dictates upsell commission',
                'tier_count' => 4,
                'base_commission' => '7.5%',
                'floor_penalty' => 'Zero upsell if retention < 80%',
                'status' => 'Archived',
                'last_run' => 'Aug 31, 2026',
            ],
        ];

        return view('dashboard', compact('statistics', 'recentModels', 'savedModels'));
    }
}
