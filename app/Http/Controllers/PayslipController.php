<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
}
