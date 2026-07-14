<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$thr = \App\Models\PayrollRun::find(7);
if($thr) {
    // Revert to DRAFT
    $thr->status = 'DRAFT';
    $thr->save();
    echo "Reverted THR 7 to DRAFT\n";
    
    // Dispatch job again synchronously to guarantee it runs
    \App\Jobs\GenerateThrJob::dispatchSync($thr->id);
    echo "Job dispatched synchronously and completed!\n";
    
    // Re-submit
    $thr->refresh();
    $thr->status = 'PENDING_FINANCE';
    $thr->save();
    
    echo "THR Run 7 Auto-submitted to PENDING_FINANCE!\n";
    
    echo "Payslip count: " . \App\Models\Payslip::where('payroll_run_id', $thr->id)->count() . "\n";
}
