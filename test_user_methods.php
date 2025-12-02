<?php
require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Bootstrap Laravel
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "Testing user methods:\n";

$superAdmin = User::where('email', 'superadmin@example.com')->first();
echo "Super Admin: {$superAdmin->name} - Role: {$superAdmin->role}\n";
echo "isSuperAdmin(): " . ($superAdmin->isSuperAdmin() ? 'true' : 'false') . "\n";
echo "isAdminWilayah(): " . ($superAdmin->isAdminWilayah() ? 'true' : 'false') . "\n";
echo "isAdminArea(): " . ($superAdmin->isAdminArea() ? 'true' : 'false') . "\n";
echo "isAdminOutlet(): " . ($superAdmin->isAdminOutlet() ? 'true' : 'false') . "\n";
echo "Office ID: " . ($superAdmin->office_id ?? 'null') . "\n";
echo "Outlet ID: " . ($superAdmin->outlet_id ?? 'null') . "\n";

$adminWilayah = User::where('email', 'adminwilayah@example.com')->first();
echo "\nAdmin Wilayah: {$adminWilayah->name} - Role: {$adminWilayah->role}\n";
echo "isSuperAdmin(): " . ($adminWilayah->isSuperAdmin() ? 'true' : 'false') . "\n";
echo "isAdminWilayah(): " . ($adminWilayah->isAdminWilayah() ? 'true' : 'false') . "\n";
echo "isAdminArea(): " . ($adminWilayah->isAdminArea() ? 'true' : 'false') . "\n";
echo "isAdminOutlet(): " . ($adminWilayah->isAdminOutlet() ? 'true' : 'false') . "\n";
echo "Office ID: " . ($adminWilayah->office_id ?? 'null') . "\n";
echo "Office loaded: " . ($adminWilayah->office ? 'Yes' : 'No') . "\n";

if ($adminWilayah->office) {
    echo "Office Name: {$adminWilayah->office->name}\n";
    echo "Office Children Count: " . $adminWilayah->office->children()->count() . "\n";
}

$adminArea = User::where('email', 'adminarea@example.com')->first();
echo "\nAdmin Area: {$adminArea->name} - Role: {$adminArea->role}\n";
echo "Office ID: " . ($adminArea->office_id ?? 'null') . "\n";
echo "Office loaded: " . ($adminArea->office ? 'Yes' : 'No') . "\n";

if ($adminArea->office) {
    echo "Office Name: {$adminArea->office->name}\n";
}