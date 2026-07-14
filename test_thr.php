<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$thr = \App\Models\PayrollRun::where('type', 'THR')->first();
if (!$thr) {
    echo "No THR run found.\n";
} else {
    echo "THR Run ID: " . $thr->id . "\n";
    echo "Status: " . $thr->status . "\n";
    echo "Approvals: " . $thr->approvals()->count() . "\n";
}
