<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/thr-runs/5/approve', 'POST');
$route = app('router')->getRoutes()->match($request);
print_r($route->parameters());
