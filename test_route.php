<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$user = \App\Models\User::first();
auth()->login($user);
$request = Illuminate\Http\Request::create('/global-search?q=adm', 'GET');
$response = $kernel->handle($request);
echo $response->getContent();