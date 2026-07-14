<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$thr = \App\Models\PayrollRun::find(7);
if($thr) {
    $thr->status = 'PENDING_FINANCE';
    $thr->save();
    
    // Create approval log
    $thr->approvals()->create([
        'status' => 'PENDING_FINANCE',
        'notes' => 'Auto-submitted temporarily',
    ]);
    
    echo "THR Run 7 auto-submitted to PENDING_FINANCE!\n";
}
