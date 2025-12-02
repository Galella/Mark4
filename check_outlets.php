<?php
require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Bootstrap Laravel
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Outlet;
use App\Models\Office;

echo "Checking outlets and their offices:\n";

$outlets = Outlet::with('office')->get();

foreach($outlets as $outlet) {
    $officeName = $outlet->office ? $outlet->office->name : 'NULL';
    echo "Outlet: {$outlet->name} (ID: {$outlet->id}) - Office: {$officeName} (Office ID in DB: {$outlet->office_id})\n";
    
    // Check if the office_id exists in the office table
    if ($outlet->office_id) {
        $officeExists = Office::where('id', $outlet->office_id)->exists();
        if (!$officeExists) {
            echo "  -> WARNING: Outlet has office_id ({$outlet->office_id}) that doesn't exist in offices table!\n";
        }
    }
}

echo "\nTotal outlets: " . $outlets->count() . "\n";