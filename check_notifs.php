<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = \App\Models\User::first();
    if ($user) {
        $notifications = $user->unreadNotifications->toArray();
        echo "Found " . count($notifications) . " unread notifications for User ID: " . $user->id . "\n";
    } else {
        echo "No users found.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
