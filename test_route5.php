<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'hr-admin'))->first();
Auth::login($user);

$thr = \App\Models\PayrollRun::find(7);
if (!$thr) { echo "No thr 7.\n"; exit; }
echo "THR 7 Status: " . $thr->status . "\n";

$request = Illuminate\Http\Request::create('/thr-runs/7/submit', 'POST');
$app->make(Illuminate\Contracts\Http\Kernel::class)->pushMiddleware(Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

$route = app('router')->getRoutes()->match($request);
echo "Route matched: " . $route->getName() . "\n";

try {
    $response = $app->handle($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 302) {
        echo "Redirect: " . $response->headers->get('Location') . "\n";
    } elseif ($response->getStatusCode() === 404) {
        echo "404 Not Found response content:\n";
        echo strip_tags($response->getContent());
    } else {
        echo "Content: " . substr($response->getContent(), 0, 500) . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
