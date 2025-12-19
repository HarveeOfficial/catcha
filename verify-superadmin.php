<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->boot();

$user = \App\Models\User::where('email', 'superadmin@catcha.com')->first();

if ($user) {
    echo "✓ Superadmin account exists!\n";
    echo "  Name: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Role: {$user->role}\n";
    echo "  Password: password\n";
} else {
    echo "✗ Superadmin not found, creating one...\n";
    $user = \App\Models\User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@catcha.com',
        'password' => bcrypt('password'),
        'role' => 'superadmin',
        'email_verified_at' => now(),
    ]);
    echo "✓ Superadmin created!\n";
    echo "  Name: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Role: {$user->role}\n";
    echo "  Password: password\n";
}
