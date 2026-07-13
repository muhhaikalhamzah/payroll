<?php

namespace App\Http\Controllers;

use App\Models\PayrollRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function exportEBupot(PayrollRun $payrollRun)
    {
        Gate::authorize('export-reports');

        if (!in_array($payrollRun->status, ['PAID', 'COMPLETED'])) {
            abort(403, 'e-Bupot can only be exported when Payroll Run is PAID.');
        }

        $payrollRun->load('payslips.employee.user');

        $fileName = 'e-Bupot_' . $payrollRun->period_month . '_' . $payrollRun->period_year . '.csv';

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = ['employee_name', 'nik', 'basic_salary', 'allowances', 'deductions', 'net_pay'];

        $callback = function() use($payrollRun, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($payrollRun->payslips as $payslip) {
                $row['employee_name'] = $payslip->employee->user->first_name . ' ' . $payslip->employee->user->last_name;
                $row['nik']           = $payslip->employee->nik;
                $row['basic_salary']  = $payslip->basic_salary;
                $row['allowances']    = $payslip->total_allowances;
                $row['deductions']    = $payslip->total_deductions;
                $row['net_pay']       = $payslip->net_pay;

                fputcsv($file, array($row['employee_name'], $row['nik'], $row['basic_salary'], $row['allowances'], $row['deductions'], $row['net_pay']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
