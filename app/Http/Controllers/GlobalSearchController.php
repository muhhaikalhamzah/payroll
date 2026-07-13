<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayrollRun;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $results = [];

        // 1. Search Menus (Static list)
        $menus = [
            ['name' => 'Dashboard', 'url' => route('dashboard.index'), 'icon' => 'bi-grid'],
            ['name' => 'Data Karyawan', 'url' => route('employees.index'), 'icon' => 'bi-person'],
            ['name' => 'Departemen', 'url' => route('departments.index'), 'icon' => 'bi-building'],
            ['name' => 'Jabatan', 'url' => route('positions.index'), 'icon' => 'bi-briefcase'],
            ['name' => 'Komponen Gaji', 'url' => route('salary-components.index'), 'icon' => 'bi-cash'],
            ['name' => 'Run Payroll', 'url' => route('payroll-runs.index'), 'icon' => 'bi-wallet2'],
            ['name' => 'Data Cuti', 'url' => route('leave-requests.index'), 'icon' => 'bi-calendar-event'],
            ['name' => 'Data Lembur', 'url' => route('overtime-requests.index'), 'icon' => 'bi-clock-history'],
            ['name' => 'Data Pinjaman', 'url' => route('employee-loans.index'), 'icon' => 'bi-cash-coin'],
            ['name' => 'Data Kehadiran', 'url' => route('attendance-records.index'), 'icon' => 'bi-fingerprint'],
            ['name' => 'Audit Logs', 'url' => route('audit-logs.index'), 'icon' => 'bi-journal-text'],
        ];

        foreach ($menus as $menu) {
            if (stripos($menu['name'], $query) !== false) {
                $results[] = [
                    'type' => 'Menu',
                    'title' => $menu['name'],
                    'subtitle' => 'Navigasi Cepat',
                    'url' => $menu['url'],
                    'icon' => $menu['icon']
                ];
            }
        }

        // 2. Search Employees
        $employees = Employee::with(['department', 'position'])
            ->where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('nik', 'like', "%{$query}%")
            ->take(5)
            ->get();

        foreach ($employees as $emp) {
            $positionName = $emp->position ? $emp->position->name : 'No Position';
            $departmentName = $emp->department ? $emp->department->name : '';
            $subtitle = 'NIK: ' . $emp->nik . ' - ' . $positionName;
            if ($departmentName) {
                $subtitle .= ' (' . $departmentName . ')';
            }

            $results[] = [
                'type' => 'Karyawan',
                'title' => $emp->first_name . ' ' . $emp->last_name,
                'subtitle' => $subtitle,
                'url' => route('employees.show', $emp->id),
                'icon' => 'bi-person-badge'
            ];
        }

        // 3. Search Payslips by Employee Name or Period
        $payslips = Payslip::with(['employee', 'payrollRun'])
            ->whereHas('employee', function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('nik', 'like', "%{$query}%");
            })
            ->orWhereHas('payrollRun', function($q) use ($query) {
                $q->where('period_month', 'like', "%{$query}%")
                  ->orWhere('period_year', 'like', "%{$query}%");
            })
            ->take(5)
            ->get();

        foreach ($payslips as $ps) {
            $monthName = date("F", mktime(0, 0, 0, $ps->payrollRun->period_month, 10));
            $results[] = [
                'type' => 'Payslip',
                'title' => 'Slip Gaji ' . $ps->employee->first_name,
                'subtitle' => 'Periode: ' . $monthName . ' ' . $ps->payrollRun->period_year . ' | Net: Rp ' . number_format($ps->net_pay, 0, ',', '.'),
                'url' => route('payslips.show', $ps->id),
                'icon' => 'bi-receipt'
            ];
        }

        return response()->json($results);
    }
}
