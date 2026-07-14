<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/thr-runs/4/submit', 'POST');
$route = app('router')->getRoutes()->match($request);

// Manually resolve the controller action
$controller = app()->make(\App\Http\Controllers\ApprovalController::class);

$reflection = new \ReflectionMethod($controller, 'submit');
$args = [];
foreach ($reflection->getParameters() as $param) {
    if ($param->getName() === 'request') {
        $args[] = $request;
    } elseif ($route->hasParameter($param->getName())) {
        $args[] = $route->parameter($param->getName());
    } else {
        $args[] = null;
    }
}
print_r($args);
