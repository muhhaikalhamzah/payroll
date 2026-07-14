<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'finance-admin'))->first();
Auth::login($user);

$request = Illuminate\Http\Request::create('/thr-runs/5', 'GET');
$app->make(Illuminate\Contracts\Http\Kernel::class)->pushMiddleware(Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
$response = $app->handle($request);
$html = $response->getContent();
preg_match_all('/action="([^"]+)"/', $html, $matches);
print_r($matches[1]);
