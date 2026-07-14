<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
Auth::login($user);

$payroll = \App\Models\PayrollRun::first();
if ($payroll) {
    echo "Payroll ID: " . $payroll->id . "\n";
    $request = Illuminate\Http\Request::create('/approvals/payroll_run/' . $payroll->id . '/submit', 'POST');
    $response = $app->handle($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
} else {
    echo "No PayrollRun.\n";
}
