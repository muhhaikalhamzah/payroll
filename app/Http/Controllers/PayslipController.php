<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipController extends Controller
{
    public function show(Payslip $payslip)
    {
        Gate::authorize('view', $payslip);
        $payslip->load([
            'employee.user', 
            'employee.position',
            'employee.department',
            'components', 
            'taxRecord', 
            'bpjsRecord',
            'payrollRun'
        ]);
        
        $title = 'Payslip Details';
        return view('payslips.show', compact('payslip', 'title'));
    }

    public function showPdf(Payslip $payslip)
    {
        Gate::authorize('view', $payslip);

        if (!in_array($payslip->payrollRun->status, ['PAID', 'COMPLETED'])) {
            abort(403, 'PDF can only be downloaded when Payroll Run is PAID.');
        }

        $payslip->load([
            'employee.user', 
            'employee.position',
            'employee.department',
            'components', 
            'taxRecord', 
            'bpjsRecord',
            'payrollRun'
        ]);

        $pdf = Pdf::loadView('payslips.pdf', compact('payslip'));
        
        $filename = 'Payslip_' . $payslip->employee->user->first_name . '_' . $payslip->payrollRun->period_month . '_' . $payslip->payrollRun->period_year . '.pdf';
        
        return $pdf->download($filename);
    }
}
