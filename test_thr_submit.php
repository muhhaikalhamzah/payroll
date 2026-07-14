<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
Auth::login($user);

$thr = \App\Models\PayrollRun::where('type', 'THR')->where('status', 'DRAFT')->first();
echo "Testing THR Run ID: " . $thr->id . "\n";

$request = Illuminate\Http\Request::create('/thr-runs/' . $thr->id . '/submit', 'POST');
$app->make(Illuminate\Contracts\Http\Kernel::class)->pushMiddleware(Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
try {
    $response = $app->handle($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    if ($response->exception) {
        echo "Exception: " . $response->exception->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
