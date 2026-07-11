<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EmployeeBankAccountController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        Gate::authorize('manage-bank-accounts');

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $isPrimary = $request->boolean('is_primary');

        DB::transaction(function () use ($employee, $validated, $isPrimary) {
            if ($isPrimary) {
                // Remove primary flag from other accounts
                $employee->bankAccounts()->update(['is_primary' => false]);
            } else {
                // If this is the only account, make it primary automatically
                if ($employee->bankAccounts()->count() === 0) {
                    $isPrimary = true;
                }
            }

            $bankAccount = $employee->bankAccounts()->create([
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'],
                'is_primary' => $isPrimary,
            ]);

            // Create Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'CREATED',
                'auditable_type' => EmployeeBankAccount::class,
                'auditable_id' => $bankAccount->id,
                'new_values' => json_encode(['bank_name' => $bankAccount->bank_name, 'account_name' => $bankAccount->account_name, 'is_primary' => $bankAccount->is_primary]), // Do not log account number directly
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return back()->with('success', 'Bank account added successfully.');
    }

    public function update(Request $request, Employee $employee, EmployeeBankAccount $bankAccount)
    {
        Gate::authorize('manage-bank-accounts');
        
        if ($bankAccount->employee_id !== $employee->id) {
            abort(403);
        }

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $isPrimary = $request->boolean('is_primary');

        DB::transaction(function () use ($employee, $bankAccount, $validated, $isPrimary) {
            $oldValues = ['bank_name' => $bankAccount->bank_name, 'account_name' => $bankAccount->account_name, 'is_primary' => $bankAccount->is_primary];

            if ($isPrimary && !$bankAccount->is_primary) {
                // Remove primary flag from other accounts
                $employee->bankAccounts()->where('id', '!=', $bankAccount->id)->update(['is_primary' => false]);
            } elseif (!$isPrimary && $bankAccount->is_primary) {
                // Cannot un-primary the only primary account if there are others, but let's just allow it or auto-assign another
                if ($employee->bankAccounts()->count() === 1) {
                    $isPrimary = true; // Must remain primary if it's the only one
                }
            }

            $bankAccount->update([
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'],
                'is_primary' => $isPrimary,
            ]);

            // Create Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'UPDATED',
                'auditable_type' => EmployeeBankAccount::class,
                'auditable_id' => $bankAccount->id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode(['bank_name' => $bankAccount->bank_name, 'account_name' => $bankAccount->account_name, 'is_primary' => $bankAccount->is_primary]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return back()->with('success', 'Bank account updated successfully.');
    }

    public function destroy(Employee $employee, EmployeeBankAccount $bankAccount)
    {
        Gate::authorize('manage-bank-accounts');

        if ($bankAccount->employee_id !== $employee->id) {
            abort(403);
        }

        DB::transaction(function () use ($employee, $bankAccount) {
            $oldValues = ['bank_name' => $bankAccount->bank_name, 'account_name' => $bankAccount->account_name, 'is_primary' => $bankAccount->is_primary];
            $wasPrimary = $bankAccount->is_primary;

            $bankAccount->delete();

            // If we deleted the primary account, make another one primary
            if ($wasPrimary) {
                $anotherAccount = $employee->bankAccounts()->first();
                if ($anotherAccount) {
                    $anotherAccount->update(['is_primary' => true]);
                }
            }

            // Create Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'DELETED',
                'auditable_type' => EmployeeBankAccount::class,
                'auditable_id' => $bankAccount->id,
                'old_values' => json_encode($oldValues),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return back()->with('success', 'Bank account deleted successfully.');
    }
}
