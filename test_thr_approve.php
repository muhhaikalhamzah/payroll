<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'finance-admin'))->first();
if (!$user) { echo "No finance admin.\n"; exit; }
Auth::login($user);

// Make sure THR Run 5 is PENDING_FINANCE
$thr = \App\Models\PayrollRun::find(5);
if (!$thr) { echo "No thr 5.\n"; exit; }
$thr->status = 'PENDING_FINANCE';
$thr->save();

// Simulate POST
$request = Illuminate\Http\Request::create('/thr-runs/5/approve', 'POST');
$request->setLaravelSession($app['session']->driver());
$app->make(Illuminate\Contracts\Http\Kernel::class)->pushMiddleware(Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
try {
    $response = $app->handle($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 302) {
        echo "Redirect: " . $response->headers->get('Location') . "\n";
    }
    if ($response->exception) {
        echo "Exception: " . $response->exception->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
