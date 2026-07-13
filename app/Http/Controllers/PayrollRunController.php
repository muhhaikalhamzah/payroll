<?php

namespace App\Http\Controllers;

use App\Models\PayrollRun;
use App\Jobs\GeneratePayrollJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class PayrollRunController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', PayrollRun::class);
        $payroll_runs = PayrollRun::with('creator')->orderBy('period_year', 'desc')->orderBy('period_month', 'desc')->get();
        $title = 'Payroll Runs';
        return view('payroll-runs.index', compact('payroll_runs', 'title'));
    }

    public function create()
    {
        Gate::authorize('create', PayrollRun::class);
        $title = 'Generate Payroll';
        return view('payroll-runs.create', compact('title'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', PayrollRun::class);
        $validated = $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2000|max:2100',
        ]);

        // Check if draft already exists
        $existing = PayrollRun::where('period_month', $validated['period_month'])
            ->where('period_year', $validated['period_year'])
            ->first();

        if ($existing) {
            if ($existing->status !== 'DRAFT') {
                return redirect()->back()->with('error', 'Cannot regenerate payroll for this period. Status is already ' . $existing->status);
            }
            $payrollRun = $existing;
        } else {
            $payrollRun = PayrollRun::create([
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);
        }

        // Dispatch background job
        GeneratePayrollJob::dispatch($payrollRun->id);

        return redirect()->route('payroll-runs.index')->with('success', 'Payroll calculation has been queued for execution.');
    }

    public function show(PayrollRun $payroll_run)
    {
        Gate::authorize('view', $payroll_run);
        $payroll_run->load(['payslips.employee.user']);
        $title = 'Payroll Details: ' . date("F", mktime(0, 0, 0, $payroll_run->period_month, 1)) . ' ' . $payroll_run->period_year;
        return view('payroll-runs.show', compact('payroll_run', 'title'));
    }
}
