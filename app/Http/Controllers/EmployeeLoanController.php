<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EmployeeLoanController extends Controller
{
    public function index()
    {
        $title = 'Employee Loans';
        $user = auth()->user();

        if ($user->hasRole(['super-admin', 'admin', 'hr-admin', 'finance-admin'])) {
            $loans = EmployeeLoan::with(['employee.user', 'approver', 'disburser'])->orderBy('created_at', 'desc')->get();
        } else {
            $employee = $user->employee;
            if (!$employee) {
                abort(403, 'User has no employee record.');
            }
            $loans = EmployeeLoan::with(['employee.user', 'approver', 'disburser'])
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('employee-loans.index', compact('title', 'loans'));
    }

    public function create()
    {
        Gate::authorize('submit-employee-loans');
        $title = 'Apply for Loan';
        $employee = auth()->user()->employee;
        if (!$employee) {
            return back()->with('error', 'You must be linked to an employee profile.');
        }

        // Determine max loan limit (3x basic salary)
        $basicSalary = $employee->salaryComponents()->where('type', 'earnings')->sum('amount');
        if ($basicSalary == 0) {
            $basicSalary = 5000000;
        }
        $maxLimit = $basicSalary * 3;

        return view('employee-loans.create', compact('title', 'employee', 'maxLimit'));
    }

    public function store(Request $request)
    {
        Gate::authorize('submit-employee-loans');
        $employee = auth()->user()->employee;
        if (!$employee) {
            abort(403, 'Unauthorized.');
        }

        $basicSalary = $employee->salaryComponents()->where('type', 'earnings')->sum('amount');
        if ($basicSalary == 0) {
            $basicSalary = 5000000;
        }
        $maxLimit = $basicSalary * 3;

        $validated = $request->validate([
            'reason' => 'required|string',
            'total_amount' => "required|numeric|min:100000|max:{$maxLimit}",
            'requested_tenor_months' => 'required|integer|min:1|max:36',
        ]);

        $monthlyInstallment = EmployeeLoan::calculateMonthlyInstallment($validated['total_amount'], $validated['requested_tenor_months']);

        EmployeeLoan::create([
            'employee_id' => $employee->id,
            'request_date' => now()->format('Y-m-d'),
            'reason' => $validated['reason'],
            'total_amount' => $validated['total_amount'],
            'requested_tenor_months' => $validated['requested_tenor_months'],
            'monthly_installment' => $monthlyInstallment,
            'remaining_balance' => $validated['total_amount'],
            'status' => 'PENDING_FINANCE'
        ]);

        return redirect()->route('employee-loans.index')->with('success', 'Loan request submitted successfully.');
    }

    public function show(EmployeeLoan $employeeLoan)
    {
        $title = 'Loan Details';
        $user = auth()->user();

        // Check view permission
        if (!$user->hasRole(['super-admin', 'admin', 'hr-admin', 'finance-admin']) && $employeeLoan->employee->user_id !== $user->id) {
            abort(403, 'Unauthorized access to loan details.');
        }

        return view('employee-loans.show', compact('title', 'employeeLoan'));
    }

    public function disburse(Request $request, $id)
    {
        Gate::authorize('disburse-employee-loans');
        
        $loan = EmployeeLoan::findOrFail($id);

        if ($loan->status !== 'APPROVED') {
            return back()->with('error', 'Only APPROVED loans can be disbursed.');
        }

        $loan->update([
            'status' => 'DISBURSED',
            'disbursed_by' => auth()->id(),
            'disbursed_at' => now()
        ]);

        return back()->with('success', 'Loan has been successfully disbursed.');
    }
}
