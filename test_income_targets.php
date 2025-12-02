<?php
require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Bootstrap Laravel
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\IncomeTarget;

// Test with super admin user
$superAdmin = User::where('email', 'superadmin@example.com')->first();
echo "Testing with Super Admin user: {$superAdmin->name}\n";

// Simulate the controller logic
try {
    // Build query
    $query = IncomeTarget::with(['outlet', 'moda', 'assignedBy']);
    
    // Get targets (this should be for super admin, so no user-based access control applied)
    $targets = $query->orderBy('target_year', 'desc')
                    ->orderBy('target_month', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
                    
    echo "Successfully retrieved " . $targets->count() . " targets from database\n";
    
    // Get outlets for filter dropdown (for super admin)
    $outlets = \App\Models\Outlet::with('office')->get();
    echo "Successfully retrieved " . $outlets->count() . " outlets for filter\n";
    
    // Get modas
    $modas = \App\Models\Moda::all();
    echo "Successfully retrieved " . $modas->count() . " modas for filter\n";
    
    echo "All data loaded successfully. The issue might be elsewhere.\n";
    
} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}