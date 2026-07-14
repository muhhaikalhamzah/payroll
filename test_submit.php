<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'hr-admin'))->first();
Auth::login($user);

$thr = \App\Models\PayrollRun::find(7);
$thr->status = 'DRAFT';
$thr->save();

$request = Illuminate\Http\Request::create('/thr-runs/7/submit', 'POST');
// Disable CSRF properly
$app->make(Illuminate\Contracts\Http\Kernel::class);
app()->instance(Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});

try {
    $response = $app->handle($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 302) {
        echo "Redirect: " . $response->headers->get('Location') . "\n";
    } elseif ($response->getStatusCode() === 404) {
        echo "404 Not Found\n";
    } elseif ($response->getStatusCode() === 403) {
        echo "403 Forbidden\n";
    } else {
        echo "Response: " . substr($response->getContent(), 0, 500) . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
