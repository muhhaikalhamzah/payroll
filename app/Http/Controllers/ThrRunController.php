<?php

namespace App\Http\Controllers;

use App\Models\PayrollRun;
use App\Jobs\GenerateThrJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ThrRunController extends Controller
{
    public function index()
    {
        abort_if(!Gate::allows('view-thr-runs'), 403);
        $thr_runs = PayrollRun::where('type', 'THR')->with('creator')->orderBy('period_year', 'desc')->orderBy('period_month', 'desc')->get();
        $title = 'THR Runs';
        return view('thr-runs.index', compact('thr_runs', 'title'));
    }

    public function create()
    {
        abort_if(!Gate::allows('create-thr-runs'), 403);
        $title = 'Generate THR';
        return view('thr-runs.create', compact('title'));
    }

    public function store(Request $request)
    {
        abort_if(!Gate::allows('create-thr-runs'), 403);
        $validated = $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2000|max:2100',
        ]);

        // Check if draft already exists
        $existing = PayrollRun::where('type', 'THR')
            ->where('period_month', $validated['period_month'])
            ->where('period_year', $validated['period_year'])
            ->first();

        if ($existing) {
            if ($existing->status !== 'DRAFT') {
                return redirect()->back()->with('error', 'Cannot regenerate THR for this period. Status is already ' . $existing->status);
            }
            $thrRun = $existing;
        } else {
            $thrRun = PayrollRun::create([
                'type' => 'THR',
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);
        }

        // Dispatch background job
        GenerateThrJob::dispatch($thrRun->id);

        return redirect()->route('thr-runs.index')->with('success', 'THR calculation has been queued for execution.');
    }

    public function show($id)
    {
        abort_if(!Gate::allows('view-thr-runs'), 403);
        $thr_run = PayrollRun::where('id', $id)->where('type', 'THR')->firstOrFail();
        
        $thr_run->load(['payslips.employee.user', 'payslips.taxRecord']);
        $title = 'THR Details: ' . date("F", mktime(0, 0, 0, $thr_run->period_month, 1)) . ' ' . $thr_run->period_year;
        return view('thr-runs.show', compact('thr_run', 'title'));
    }
}
