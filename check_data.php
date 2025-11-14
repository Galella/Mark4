<?php
// bootstrap.php - File untuk memeriksa data user
require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Buat request kosong
$request = \Illuminate\Http\Request::createFromGlobals();
$app->bind('request', function () use ($request) {
    return $request;
});

// Ambil kernel
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ambil data
$users = \App\Models\User::with(['office', 'outlet'])->get();

echo "<h2>Daftar User</h2>\n";
foreach($users as $user) {
    echo "<p><strong>Nama:</strong> {$user->name}<br>";
    echo "<strong>Email:</strong> {$user->email}<br>";
    echo "<strong>Role:</strong> {$user->role}<br>";
    if($user->office) {
        echo "<strong>Office:</strong> {$user->office->name} ({$user->office->type})<br>";
    }
    if($user->outlet) {
        echo "<strong>Outlet:</strong> {$user->outlet->name}<br>";
    }
    echo "</p><hr>\n";
}

echo "<h2>Daftar Office</h2>\n";
$offices = \App\Models\Office::all();
foreach($offices as $office) {
    echo "<p><strong>Nama:</strong> {$office->name}<br>";
    echo "<strong>Code:</strong> {$office->code}<br>";
    echo "<strong>Tipe:</strong> {$office->type}<br>";
    if($office->parent) {
        echo "<strong>Parent:</strong> {$office->parent->name}<br>";
    }
    echo "</p><hr>\n";
}