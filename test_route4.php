<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'finance-admin'))->first();
Auth::login($user);

// Make sure THR Run 5 is PENDING_FINANCE
$thr = \App\Models\PayrollRun::find(5);
if (!$thr) { echo "No thr 5.\n"; exit; }
$thr->status = 'PENDING_FINANCE';
$thr->save();

// We will inject a middleware that intercepts the request and handles it manually to see if we get a 404
$request = Illuminate\Http\Request::create('/thr-runs/5/approve', 'POST');
$app->make(Illuminate\Contracts\Http\Kernel::class)->pushMiddleware(Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

$route = app('router')->getRoutes()->match($request);
echo "Route matched: " . $route->getName() . "\n";
echo "Parameters: " . json_encode($route->parameters()) . "\n";

try {
    $response = $app->handle($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 302) {
        echo "Redirect: " . $response->headers->get('Location') . "\n";
    } elseif ($response->getStatusCode() === 404) {
        echo "404 Not Found response content:\n";
        echo strip_tags($response->getContent());
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
