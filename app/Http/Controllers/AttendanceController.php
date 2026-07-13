<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Jobs\ImportAttendanceJob;

class AttendanceController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', AttendanceRecord::class);
        $attendances = AttendanceRecord::with('employee')->orderBy('date', 'desc')->get();
        $title = 'Attendance Records';
        return view('attendance-records.index', compact('attendances', 'title'));
    }

    public function create()
    {
        Gate::authorize('create', AttendanceRecord::class);
        $title = 'Create Attendance Record';
        $employees = Employee::all();
        return view('attendance-records.create', compact('employees', 'title'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', AttendanceRecord::class);
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,alfa,izin,sakit,cuti',
            'remarks' => 'nullable|string|max:255',
        ]);

        AttendanceRecord::create($validated);
        return redirect()->route('attendance-records.index')->with('success', 'Attendance record created successfully.');
    }

    public function edit(AttendanceRecord $attendance_record)
    {
        Gate::authorize('update', $attendance_record);
        $title = 'Edit Attendance Record';
        $employees = Employee::all();
        return view('attendance-records.edit', compact('attendance_record', 'employees', 'title'));
    }

    public function update(Request $request, AttendanceRecord $attendance_record)
    {
        Gate::authorize('update', $attendance_record);

        if (\App\Models\PayrollRun::isLockedByPaid($attendance_record->employee_id, $attendance_record->date)) {
            $month = date('n', strtotime($attendance_record->date));
            $year = date('Y', strtotime($attendance_record->date));
            return back()->with('error', "Data ini tidak bisa diubah karena sudah tercakup dalam payroll yang telah dibayarkan periode {$month}-{$year}");
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'status' => 'required|in:hadir,alfa,izin,sakit,cuti',
            'remarks' => 'nullable|string|max:255',
        ]);
        
        $attendance_record->update($validated);
        return redirect()->route('attendance-records.index')->with('success', 'Attendance record updated successfully.');
    }

    public function destroy(AttendanceRecord $attendance_record)
    {
        Gate::authorize('delete', $attendance_record);

        if (\App\Models\PayrollRun::isLockedByPaid($attendance_record->employee_id, $attendance_record->date)) {
            $month = date('n', strtotime($attendance_record->date));
            $year = date('Y', strtotime($attendance_record->date));
            return back()->with('error', "Data ini tidak bisa dihapus karena sudah tercakup dalam payroll yang telah dibayarkan periode {$month}-{$year}");
        }

        $attendance_record->delete();
        return redirect()->route('attendance-records.index')->with('success', 'Attendance record deleted successfully.');
    }

    public function importForm()
    {
        Gate::authorize('create', AttendanceRecord::class);
        $title = 'Import Attendance';
        return view('attendance-records.import', compact('title'));
    }

    public function import(Request $request)
    {
        Gate::authorize('create', AttendanceRecord::class);
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls'
        ]);

        $path = $request->file('file')->store('imports');
        $batchId = (string) \Illuminate\Support\Str::uuid();
        
        // Initialize cache
        \Illuminate\Support\Facades\Cache::put('import_attendance_' . $batchId, [
            'status' => 'processing',
            'success' => 0,
            'errors' => [],
            'total' => 0
        ], now()->addHours(2));
        
        // Dispatch job
        ImportAttendanceJob::dispatch(storage_path('app/' . $path), $batchId);

        return redirect()->route('attendance-records.import-form')
            ->with('import_batch_id', $batchId)
            ->with('success', 'Attendance import has been queued and is processing.');
    }

    public function importStatus($batchId)
    {
        Gate::authorize('create', AttendanceRecord::class);
        $status = \Illuminate\Support\Facades\Cache::get('import_attendance_' . $batchId);
        
        if (!$status) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json($status);
    }
}
