<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'finance-admin'))->first();
Auth::login($user);

$controller = new \App\Http\Controllers\ApprovalController();
$request = new \Illuminate\Http\Request();
try {
    $response = $controller->approve($request, 'payroll_run', 5);
    print_r($response);
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
