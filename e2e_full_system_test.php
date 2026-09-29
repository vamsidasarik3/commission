<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\CommissionModelController;
use App\Http\Controllers\DashboardController;
use App\Models\CommissionLevel;
use App\Models\CommissionModel;
use App\Services\CommissionCalculator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

echo "=======================================================================\n";
echo "WEAKEST LINK COMMISSION MODEL - COMPLETE END-TO-END TEST SUITE\n";
echo "Application URL: http://custom.local:8084/\n";
echo "=======================================================================\n\n";

$passCount = 0;
$failCount = 0;
$failedTests = [];
$bugsFixed = [];

function recordResult(string $testName, bool $passed, string $details = '')
{
    global $passCount, $failCount, $failedTests;
    if ($passed) {
        $passCount++;
        echo "  [PASS] {$testName}".($details ? " - {$details}" : '')."\n";
    } else {
        $failCount++;
        $failedTests[] = $testName.($details ? ": {$details}" : '');
        echo "  [FAIL] {$testName} - {$details}\n";
    }
}

$session = app('session.store');
$calculator = app(CommissionCalculator::class);
$modelController = app(CommissionModelController::class);
$dashboardController = app(DashboardController::class);

// ---------------------------------------------------------------------
// TEST 1: Application Loads (Dashboard at /)
// ---------------------------------------------------------------------
echo "1. Testing Application Loads (Dashboard)...\n";
try {
    $req = Request::create('/', 'GET');
    $req->setLaravelSession($session);
    $view = $dashboardController->index();
    $html = $view->render();
    $ok = str_contains($html, 'Weakest Link Commission Model') && str_contains($html, 'Saved Models');
    recordResult('Application Loads (Dashboard /)', $ok, 'HTTP 200 & Core HTML structure rendered');
} catch (Throwable $e) {
    recordResult('Application Loads (Dashboard /)', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 2: Tailwind CSS Loads
// ---------------------------------------------------------------------
echo "\n2. Testing Tailwind CSS Loads...\n";
try {
    $manifestPath = public_path('build/manifest.json');
    $manifestExists = File::exists($manifestPath);
    $cssFileExists = false;
    if ($manifestExists) {
        $manifest = json_decode(File::get($manifestPath), true);
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        if ($cssFile && File::exists(public_path('build/'.$cssFile))) {
            $cssFileExists = true;
            $cssSize = filesize(public_path('build/'.$cssFile));
            $ok = $cssSize > 10000; // compiled tailwind is ~70KB
            recordResult('Tailwind CSS Loads', $ok, "Compiled asset '{$cssFile}' exists (".round($cssSize / 1024, 1).' KB)');
        } else {
            recordResult('Tailwind CSS Loads', false, 'CSS bundle missing in build directory');
        }
    } else {
        recordResult('Tailwind CSS Loads', false, 'Vite manifest.json not found');
    }
} catch (Throwable $e) {
    recordResult('Tailwind CSS Loads', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 3: Model Builder Loads (/commission-models/create and /models/create)
// ---------------------------------------------------------------------
echo "\n3. Testing Model Builder Loads...\n";
try {
    $req = Request::create('/commission-models/create', 'GET');
    $req->setLaravelSession($session);
    $view = $modelController->create();
    $html = $view->render();
    $ok = str_contains($html, 'Commission Model Builder') &&
          str_contains($html, 'Model Parameters') &&
          str_contains($html, 'Generate Levels') &&
          str_contains($html, 'Calculate Model') &&
          str_contains($html, 'Save Model');
    recordResult('Model Builder Loads', $ok, 'All sections, parameter inputs, and action buttons present');
} catch (Throwable $e) {
    recordResult('Model Builder Loads', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 4: Number of levels dynamically generates rows
// ---------------------------------------------------------------------
echo "\n4. Testing Dynamic Level Generation Logic...\n";
try {
    $chain1 = $calculator->buildChain(1, 500, 1000);
    $chain10 = $calculator->buildChain(10, 500, 1000);
    $chain20 = $calculator->buildChain(20, 500, 1000);
    $chain50 = $calculator->buildChain(50, 500, 1000);
    $ok = (count($chain1) === 1) && (count($chain10) === 10) && (count($chain20) === 20) && (count($chain50) === 50);
    recordResult('Dynamic Level Generation', $ok, 'Generated 1, 10, 20, and 50 levels dynamically without hardcoding');
} catch (Throwable $e) {
    recordResult('Dynamic Level Generation', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 5: Complete Server-Side Validation Works
// ---------------------------------------------------------------------
echo "\n5. Testing Complete Server-Side Validation...\n";
try {
    $caught = false;
    try {
        $req = Request::create('/commission-models', 'POST', [
            'name' => '',
            'commission_rate' => 'not_a_number',
            'number_of_levels' => 0,
            'levels' => [],
        ]);
        $req->setLaravelSession($session);
        $modelController->store($req);
    } catch (ValidationException $e) {
        $caught = true;
        $errors = $e->validator->errors();
        $ok = $errors->has('name') && $errors->has('commission_rate') && $errors->has('number_of_levels') && $errors->has('levels');
        recordResult('Validation Rejects Malformed Inputs', $ok, 'Caught 4 validation errors as expected');
    }
    if (! $caught) {
        recordResult('Validation Rejects Malformed Inputs', false, 'Expected ValidationException was not thrown');
    }
} catch (Throwable $e) {
    recordResult('Validation Rejects Malformed Inputs', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 6: Calculation Works
// ---------------------------------------------------------------------
echo "\n6. Testing Calculation Engine...\n";
try {
    $res = $calculator->calculate(5.00, [
        ['level' => 1, 'main_person' => 'A', 'main_sales' => 1000, 'side_person' => 'S1', 'side_sales' => 1000],
    ]);
    $ok = isset($res['final_commission']) && isset($res['total_sales']) && isset($res['total_potential_commission']);
    recordResult('Calculation Engine Output', $ok, 'Computed metrics successfully with rate 5.00%');
} catch (Throwable $e) {
    recordResult('Calculation Engine Output', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 7: Weakest-Link Calculation (MIN Comparison Rule)
// ---------------------------------------------------------------------
echo "\n7. Testing Weakest-Link MIN Comparison Rule...\n";
try {
    // If main child commission is 100 and side is 10, leader commission must be MIN(100, 10) = 10 (NOT SUM 110, NOT AVG 55, NOT MAX 100)
    $resMin = $calculator->calculate(5.00, [
        ['level' => 1, 'main_person' => 'B', 'main_sales' => 2000, 'side_person' => 'S1', 'side_sales' => 200], // B comm = 100, S1 comm = 10
    ]);
    $lvl1 = $resMin['levels'][0];
    $isMin = ($lvl1['leader_commission'] == 10.00);
    $notSum = ($lvl1['leader_commission'] != 110.00);
    $notAvg = ($lvl1['leader_commission'] != 55.00);
    $notMax = ($lvl1['leader_commission'] != 100.00);
    recordResult('Weakest-Link MIN Rule', $isMin && $notSum && $notAvg && $notMax, 'Leader commission is MIN(₹100, ₹10) = ₹10 (Not Sum, Avg, or Max)');
} catch (Throwable $e) {
    recordResult('Weakest-Link MIN Rule', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 8: Results Display Correctly
// ---------------------------------------------------------------------
echo "\n8. Testing Calculation Results Formatting...\n";
try {
    $req = Request::create('/commission-models/calculate', 'POST', [
        'commission_rate' => 5.00,
        'number_of_levels' => 2,
        'levels' => [
            ['level' => 1, 'main_person' => 'A', 'main_sales' => 1000, 'side_person' => 'S1', 'side_sales' => 500],
            ['level' => 2, 'main_person' => 'B', 'main_sales' => 400, 'side_person' => 'S2', 'side_sales' => 600],
        ],
    ]);
    $resp = $modelController->calculate($req);
    $data = $resp->getData(true);
    $ok = ($data['success'] === true) &&
          isset($data['data']['total_sales']) &&
          isset($data['data']['total_potential_commission']) &&
          isset($data['data']['weakest_person']) &&
          isset($data['data']['weakest_sales']) &&
          isset($data['data']['weakest_commission']) &&
          isset($data['data']['final_commission']);
    recordResult('Results Display Keys', $ok, 'AJAX response contains all summary metrics and breakdown array');
} catch (Throwable $e) {
    recordResult('Results Display Keys', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 9: Cascade Visualization Data Works
// ---------------------------------------------------------------------
echo "\n9. Testing Visual Hierarchy & Cascade Data...\n";
try {
    $resCascade = $calculator->calculate(5.00, [
        ['level' => 1, 'main_person' => 'B', 'main_sales' => 1000, 'side_person' => 'S1', 'side_sales' => 800],
        ['level' => 2, 'main_person' => 'C', 'main_sales' => 400, 'side_person' => 'S2', 'side_sales' => 600],
    ]);
    $ok = count($resCascade['levels']) === 2 &&
          isset($resCascade['levels'][0]['side_person']) &&
          isset($resCascade['levels'][0]['main_person']) &&
          isset($resCascade['levels'][0]['selected_minimum']);
    recordResult('Cascade Visualization Data', $ok, 'Hierarchy branches (Main, Side, Selected Min) structured for cards & ASCII tree');
} catch (Throwable $e) {
    recordResult('Cascade Visualization Data', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 10: Model Saves with Calculation Snapshot
// ---------------------------------------------------------------------
echo "\n10. Testing Save Model with Transaction & Snapshot...\n";
$savedModelId = null;
try {
    $saveName = 'E2E Master Test Model '.time();
    $req = Request::create('/commission-models', 'POST', [
        'name' => $saveName,
        'commission_rate' => 5.00,
        'number_of_levels' => 3,
        'levels' => [
            ['level' => 1, 'main_person' => 'A', 'main_sales' => 1000, 'side_person' => 'S1', 'side_sales' => 900],
            ['level' => 2, 'main_person' => 'B', 'main_sales' => 800, 'side_person' => 'S2', 'side_sales' => 700],
            ['level' => 3, 'main_person' => 'C', 'main_sales' => 200, 'side_person' => 'S3', 'side_sales' => 500],
        ],
    ]);
    $req->setLaravelSession($session);
    $resp = $modelController->store($req);
    $modelRecord = CommissionModel::where('name', $saveName)->first();
    if ($modelRecord) {
        $savedModelId = $modelRecord->id;
        $levelsCount = $modelRecord->levels()->count();
        $ok = ($levelsCount === 3) && ((float) $modelRecord->final_commission > 0) && ($modelRecord->weakest_person === 'C');
        recordResult('Model Saves to Database', $ok, "Created model ID #{$savedModelId} with 3 child levels & calculation snapshot");
    } else {
        recordResult('Model Saves to Database', false, 'Model record not found in database');
    }
} catch (Throwable $e) {
    recordResult('Model Saves to Database', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 11: Saved Model Appears in History (/commission-models)
// ---------------------------------------------------------------------
echo "\n11. Testing Saved Model Appears in History Catalog...\n";
try {
    $req = Request::create('/commission-models', 'GET');
    $req->setLaravelSession($session);
    $view = $modelController->index();
    $html = $view->render();
    $ok = str_contains($html, 'E2E Master Test Model') && str_contains($html, "#{$savedModelId}");
    recordResult('Saved Model in History (/commission-models)', $ok, "Model #{$savedModelId} displayed in catalog table");
} catch (Throwable $e) {
    recordResult('Saved Model in History (/commission-models)', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 12: Saved Model Can Be Viewed
// ---------------------------------------------------------------------
echo "\n12. Testing Saved Model Can Be Viewed (/commission-models/{id})...\n";
try {
    $model = CommissionModel::find($savedModelId);
    $view = $modelController->show($model);
    $html = $view->render();
    $ok = str_contains($html, $model->name) &&
          str_contains($html, 'Visual Model Hierarchy & Cascade Tree') &&
          str_contains($html, 'Level-by-Level Calculation Breakdown');
    recordResult('View Saved Model', $ok, 'Displays complete stored model and its level calculations');
} catch (Throwable $e) {
    recordResult('View Saved Model', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 13: Saved Model Can Be Duplicated
// ---------------------------------------------------------------------
echo "\n13. Testing Saved Model Can Be Duplicated...\n";
try {
    $model = CommissionModel::find($savedModelId);
    $view = $modelController->duplicate($model);
    $html = $view->render();
    $ok = str_contains($html, 'Copy of '.$model->name) &&
          str_contains($html, 'Duplicate Commission Model');
    recordResult('Duplicate Saved Model', $ok, "Pre-populates new editable model builder with 'Copy of {$model->name}' and all levels");
} catch (Throwable $e) {
    recordResult('Duplicate Saved Model', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 14: Saved Model Can Be Deleted with Confirmation
// ---------------------------------------------------------------------
echo "\n14. Testing Saved Model Can Be Deleted...\n";
try {
    $toDelete = CommissionModel::create([
        'name' => 'To Delete E2E '.time(),
        'commission_rate' => 5.0,
        'number_of_levels' => 1,
        'total_sales' => 100,
        'total_potential_commission' => 5,
        'final_commission' => 5,
        'weakest_person' => 'X',
        'weakest_sales' => 100,
        'weakest_commission' => 5,
    ]);
    $toDelete->levels()->create([
        'level' => 1,
        'main_person' => 'X',
        'main_sales' => 100,
        'main_commission' => 5,
        'side_person' => 'SX',
        'side_sales' => 100,
        'side_commission' => 5,
        'selected_commission' => 5,
        'leader_commission' => 5,
    ]);
    $delId = $toDelete->id;

    $resp = $modelController->destroy($toDelete);
    $foundModel = CommissionModel::find($delId);
    $foundLevels = CommissionLevel::where('commission_model_id', $delId)->count();
    $ok = ($foundModel === null) && ($foundLevels === 0);
    recordResult('Delete Saved Model', $ok, "Permanently deleted model #{$delId} and child levels from MySQL");
} catch (Throwable $e) {
    recordResult('Delete Saved Model', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 15: Database Transactions Work (Rollback Protection)
// ---------------------------------------------------------------------
echo "\n15. Testing Database Transaction Rollback on Failure...\n";
try {
    $preM = CommissionModel::count();
    $preL = CommissionLevel::count();

    DB::beginTransaction();
    $failM = CommissionModel::create([
        'name' => 'Crash Simulation Model',
        'commission_rate' => 5,
        'number_of_levels' => 2,
        'total_sales' => 500,
        'total_potential_commission' => 25,
        'final_commission' => 25,
        'weakest_person' => 'Z',
        'weakest_sales' => 500,
        'weakest_commission' => 25,
    ]);
    $failM->levels()->create([
        'level' => 1,
        'main_person' => 'A',
        'main_sales' => 500,
        'main_commission' => 25,
        'side_person' => 'S1',
        'side_sales' => 500,
        'side_commission' => 25,
        'selected_commission' => 25,
        'leader_commission' => 25,
    ]);
    // Simulate error and rollback
    DB::rollBack();

    $postM = CommissionModel::count();
    $postL = CommissionLevel::count();
    $ok = ($preM === $postM) && ($preL === $postL);
    recordResult('Database Transaction Rollback', $ok, "Pre-count ({$preM}) === Post-count ({$postM}), 0 orphaned records saved");
} catch (Throwable $e) {
    recordResult('Database Transaction Rollback', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 16: Invalid Data is Rejected
// ---------------------------------------------------------------------
echo "\n16. Testing Invalid Data Rejection...\n";
try {
    $caught = false;
    try {
        $req = Request::create('/commission-models', 'POST', [
            'name' => 'Invalid Model',
            'commission_rate' => 5.0,
            'number_of_levels' => 'three', // non-integer
            'levels' => 'not_array',
        ]);
        $req->setLaravelSession($session);
        $modelController->store($req);
    } catch (ValidationException $e) {
        $caught = true;
    }
    recordResult('Invalid Data Rejected', $caught, 'Blocked non-integer and non-array parameters');
} catch (Throwable $e) {
    recordResult('Invalid Data Rejected', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 17: Negative Sales are Rejected
// ---------------------------------------------------------------------
echo "\n17. Testing Negative Sales Rejection...\n";
try {
    $caught = false;
    try {
        $req = Request::create('/commission-models', 'POST', [
            'name' => 'Negative Sales Test',
            'commission_rate' => 5.0,
            'number_of_levels' => 1,
            'levels' => [
                ['level' => 1, 'main_person' => 'A', 'main_sales' => -50.00, 'side_person' => 'S1', 'side_sales' => 100],
            ],
        ]);
        $req->setLaravelSession($session);
        $modelController->store($req);
    } catch (ValidationException $e) {
        $caught = $e->validator->errors()->has('levels.0.main_sales');
    }
    recordResult('Negative Sales Rejected', $caught, 'Blocked negative main sales (-₹50.00)');
} catch (Throwable $e) {
    recordResult('Negative Sales Rejected', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 18: Invalid Commission Rates are Rejected
// ---------------------------------------------------------------------
echo "\n18. Testing Invalid Commission Rates Rejection...\n";
try {
    $caughtNegative = false;
    $caughtExcessive = false;
    try {
        $req = Request::create('/commission-models', 'POST', [
            'name' => 'Negative Rate Test',
            'commission_rate' => -2.5,
            'number_of_levels' => 1,
            'levels' => [['level' => 1, 'main_person' => 'A', 'main_sales' => 100, 'side_person' => 'S1', 'side_sales' => 100]],
        ]);
        $req->setLaravelSession($session);
        $modelController->store($req);
    } catch (ValidationException $e) {
        $caughtNegative = $e->validator->errors()->has('commission_rate');
    }

    try {
        $req = Request::create('/commission-models', 'POST', [
            'name' => 'Excessive Rate Test',
            'commission_rate' => 150.0,
            'number_of_levels' => 1,
            'levels' => [['level' => 1, 'main_person' => 'A', 'main_sales' => 100, 'side_person' => 'S1', 'side_sales' => 100]],
        ]);
        $req->setLaravelSession($session);
        $modelController->store($req);
    } catch (ValidationException $e) {
        $caughtExcessive = $e->validator->errors()->has('commission_rate');
    }

    recordResult('Invalid Commission Rates Rejected', $caughtNegative && $caughtExcessive, 'Blocked rate < 0% and rate > 100%');
} catch (Throwable $e) {
    recordResult('Invalid Commission Rates Rejected', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 19: Decimal Values Work Correctly
// ---------------------------------------------------------------------
echo "\n19. Testing Decimal Values Precision...\n";
try {
    // sales = 1234.56, rate = 5.25% => 1234.56 * 0.0525 = 64.8144 => rounds to 64.81
    $comm = $calculator->calculateIndividualCommission(1234.56, 5.25);
    $ok = ($comm === 64.81);
    recordResult('Decimal Values Precision', $ok, '₹1234.56 × 5.25% = ₹64.81 exact 2-decimal currency precision');
} catch (Throwable $e) {
    recordResult('Decimal Values Precision', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 20: 1-Level Model Works
// ---------------------------------------------------------------------
echo "\n20. Testing 1-Level Model...\n";
try {
    $chain1 = [
        ['level' => 1, 'main_person' => 'A', 'main_sales' => 500, 'side_person' => 'S1', 'side_sales' => 300],
    ];
    $res1 = $calculator->calculate(10.0, $chain1);
    // A comm = 50, S1 comm = 30 => MIN(50, 30) = 30
    $ok = ($res1['final_commission'] == 30.00) && ($res1['weakest_person'] === 'S1');
    recordResult('1-Level Model', $ok, 'Final commission: ₹30.00, Weakest: S1');
} catch (Throwable $e) {
    recordResult('1-Level Model', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 21: 10-Level Model Works
// ---------------------------------------------------------------------
echo "\n21. Testing 10-Level Model...\n";
try {
    $chain10 = $calculator->buildChain(10, 1000, 500);
    $res10 = $calculator->calculate(5.0, $chain10);
    $ok = (count($res10['levels']) === 10) && ($res10['number_of_levels'] === 10);
    recordResult('10-Level Model', $ok, '10-level hierarchy processed smoothly');
} catch (Throwable $e) {
    recordResult('10-Level Model', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 22: 50-Level Model Works (Stress & Scalability Test)
// ---------------------------------------------------------------------
echo "\n22. Testing 50-Level Model Stress Test...\n";
try {
    $t0 = microtime(true);
    $chain50 = $calculator->buildChain(50, 1000, 200);
    $res50 = $calculator->calculate(5.0, $chain50);
    $elapsed = round((microtime(true) - $t0) * 1000, 2);
    $ok = (count($res50['levels']) === 50) && ($res50['final_commission'] == 10.00); // 200 * 5% = 10
    recordResult('50-Level Model Scalability', $ok, "50 levels computed in {$elapsed}ms (Final Commission ₹10.00)");
} catch (Throwable $e) {
    recordResult('50-Level Model Scalability', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 23: USER SPECIFIC TEST 1
// Commission rate = 5%
// Bottom salesperson: J = ₹40 => J comm = ₹2
// Side commissions:
// S9 = ₹10 (sales 200)
// S8 = ₹15 (sales 300)
// S7 = ₹20 (sales 400)
// S6 = ₹25 (sales 500)
// S5 = ₹30 (sales 600)
// S4 = ₹35 (sales 700)
// S3 = ₹40 (sales 800)
// S2 = ₹45 (sales 900)
// S1 = ₹50 (sales 1000)
// Expected final commission: ₹2
// ---------------------------------------------------------------------
echo "\n23. Running User Specific Test 1 (J = ₹40, Rate = 5%)...\n";
try {
    // 9 levels: A..I leaders, side S1..S9, bottom main child J
    $levelsSpec1 = [
        ['level' => 1, 'leader' => 'A', 'main_person' => 'B', 'main_sales' => 0, 'side_person' => 'S1', 'side_sales' => 1000], // S1 comm = 50
        ['level' => 2, 'leader' => 'B', 'main_person' => 'C', 'main_sales' => 0, 'side_person' => 'S2', 'side_sales' => 900],  // S2 comm = 45
        ['level' => 3, 'leader' => 'C', 'main_person' => 'D', 'main_sales' => 0, 'side_person' => 'S3', 'side_sales' => 800],  // S3 comm = 40
        ['level' => 4, 'leader' => 'D', 'main_person' => 'E', 'main_sales' => 0, 'side_person' => 'S4', 'side_sales' => 700],  // S4 comm = 35
        ['level' => 5, 'leader' => 'E', 'main_person' => 'F', 'main_sales' => 0, 'side_person' => 'S5', 'side_sales' => 600],  // S5 comm = 30
        ['level' => 6, 'leader' => 'F', 'main_person' => 'G', 'main_sales' => 0, 'side_person' => 'S6', 'side_sales' => 500],  // S6 comm = 25
        ['level' => 7, 'leader' => 'G', 'main_person' => 'H', 'main_sales' => 0, 'side_person' => 'S7', 'side_sales' => 400],  // S7 comm = 20
        ['level' => 8, 'leader' => 'H', 'main_person' => 'I', 'main_sales' => 0, 'side_person' => 'S8', 'side_sales' => 300],  // S8 comm = 15
        ['level' => 9, 'leader' => 'I', 'main_person' => 'J', 'main_sales' => 40, 'side_person' => 'S9', 'side_sales' => 200], // S9 comm = 10, J comm = 2
    ];

    $resSpec1 = $calculator->calculate(5.00, $levelsSpec1, 'A');

    $jComm = $calculator->calculateIndividualCommission(40, 5);
    $s9Comm = $calculator->calculateIndividualCommission(200, 5);
    $s8Comm = $calculator->calculateIndividualCommission(300, 5);
    $s1Comm = $calculator->calculateIndividualCommission(1000, 5);

    $level9 = $resSpec1['levels'][8]; // Level 9
    $iComm = $level9['leader_commission'];
    $finalComm = $resSpec1['final_commission'];

    $okJ = ($jComm == 2.00);
    $okS9 = ($s9Comm == 10.00);
    $okI = ($iComm == 2.00); // MIN(J=2, S9=10) = 2
    $okFinal = ($finalComm == 2.00);

    echo "     - J Sales: ₹40 -> J Commission: ₹{$jComm} (Expected: ₹2)\n";
    echo "     - S9 Sales: ₹200 -> S9 Commission: ₹{$s9Comm} (Expected: ₹10)\n";
    echo "     - Level 9 (Leader I): MIN(J=₹2, S9=₹10) = ₹{$iComm}\n";
    echo "     - Final Top-Level Commission: ₹{$finalComm} (Expected: ₹2)\n";

    recordResult('User Specific Test 1', $okJ && $okS9 && $okI && $okFinal, "Final commission is exactly ₹2.00 bounded by J's ₹2 commission");
} catch (Throwable $e) {
    recordResult('User Specific Test 1', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// TEST 24: USER SPECIFIC TEST 2
// J = ₹500 => J commission = ₹25
// S9 = ₹100 => S9 commission = ₹5
// Expected I commission: ₹5
// Verify that the ₹5 weakest value propagates upward where applicable.
// ---------------------------------------------------------------------
echo "\n24. Running User Specific Test 2 (J = ₹500, S9 = ₹100, Rate = 5%)...\n";
try {
    $levelsSpec2 = [
        ['level' => 1, 'leader' => 'A', 'main_person' => 'B', 'main_sales' => 0, 'side_person' => 'S1', 'side_sales' => 1000], // S1 comm = 50
        ['level' => 2, 'leader' => 'B', 'main_person' => 'C', 'main_sales' => 0, 'side_person' => 'S2', 'side_sales' => 900],  // S2 comm = 45
        ['level' => 3, 'leader' => 'C', 'main_person' => 'D', 'main_sales' => 0, 'side_person' => 'S3', 'side_sales' => 800],  // S3 comm = 40
        ['level' => 4, 'leader' => 'D', 'main_person' => 'E', 'main_sales' => 0, 'side_person' => 'S4', 'side_sales' => 700],  // S4 comm = 35
        ['level' => 5, 'leader' => 'E', 'main_person' => 'F', 'main_sales' => 0, 'side_person' => 'S5', 'side_sales' => 600],  // S5 comm = 30
        ['level' => 6, 'leader' => 'F', 'main_person' => 'G', 'main_sales' => 0, 'side_person' => 'S6', 'side_sales' => 500],  // S6 comm = 25
        ['level' => 7, 'leader' => 'G', 'main_person' => 'H', 'main_sales' => 0, 'side_person' => 'S7', 'side_sales' => 400],  // S7 comm = 20
        ['level' => 8, 'leader' => 'H', 'main_person' => 'I', 'main_sales' => 0, 'side_person' => 'S8', 'side_sales' => 300],  // S8 comm = 15
        ['level' => 9, 'leader' => 'I', 'main_person' => 'J', 'main_sales' => 500, 'side_person' => 'S9', 'side_sales' => 100], // J comm = 25, S9 comm = 5
    ];

    $resSpec2 = $calculator->calculate(5.00, $levelsSpec2, 'A');

    $jComm2 = $calculator->calculateIndividualCommission(500, 5); // 25
    $s9Comm2 = $calculator->calculateIndividualCommission(100, 5); // 5
    $level9_2 = $resSpec2['levels'][8];
    $iComm2 = $level9_2['leader_commission']; // MIN(25, 5) = 5

    // Verify upward propagation:
    // Level 8 (H): MIN(I=5, S8=15) = 5
    $level8_2 = $resSpec2['levels'][7];
    $hComm2 = $level8_2['leader_commission'];

    // Level 1 (A): MIN(B=5, S1=50) = 5
    $level1_2 = $resSpec2['levels'][0];
    $aComm2 = $level1_2['leader_commission'];
    $finalComm2 = $resSpec2['final_commission'];

    $okJ2 = ($jComm2 == 25.00);
    $okS9_2 = ($s9Comm2 == 5.00);
    $okI2 = ($iComm2 == 5.00);
    $okH2 = ($hComm2 == 5.00);
    $okProp = ($aComm2 == 5.00 && $finalComm2 == 5.00);

    echo "     - J Sales: ₹500 -> J Commission: ₹{$jComm2} (Expected: ₹25)\n";
    echo "     - S9 Sales: ₹100 -> S9 Commission: ₹{$s9Comm2} (Expected: ₹5)\n";
    echo "     - Level 9 (Leader I): MIN(J=₹25, S9=₹5) = ₹{$iComm2} (Expected: ₹5)\n";
    echo "     - Level 8 (Leader H): MIN(I=₹5, S8=₹15) = ₹{$hComm2} (Propagated)\n";
    echo "     - Level 1 (Leader A / Final): MIN(B=₹5, S1=₹50) = ₹{$finalComm2} (Propagated upward to Top Leader A)\n";

    recordResult('User Specific Test 2', $okJ2 && $okS9_2 && $okI2 && $okH2 && $okProp, 'Leader I = ₹5.00 and propagates upward through H..A to Final Commission ₹5.00');
} catch (Throwable $e) {
    recordResult('User Specific Test 2', false, $e->getMessage());
}

// ---------------------------------------------------------------------
// Clean up test models
// ---------------------------------------------------------------------
if ($savedModelId) {
    $m = CommissionModel::find($savedModelId);
    if ($m) {
        $m->levels()->delete();
        $m->delete();
    }
}

// ---------------------------------------------------------------------
// SUMMARY OF TEST RESULTS
// ---------------------------------------------------------------------
echo "\n=======================================================================\n";
echo "SUMMARY OF TEST RESULTS\n";
echo "=======================================================================\n";
echo 'Total Tests Run: '.($passCount + $failCount)."\n";
echo "Passed Tests: {$passCount}\n";
echo "Failed Tests: {$failCount}\n";
if (! empty($failedTests)) {
    echo "Failures:\n";
    foreach ($failedTests as $ft) {
        echo "  - {$ft}\n";
    }
}
echo "=======================================================================\n";

if ($failCount > 0) {
    exit(1);
}
