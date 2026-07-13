<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LeaveType;
use Illuminate\Support\Facades\Gate;

class LeaveTypeController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-leave-types');
        $title = 'Leave Types';
        $leaveTypes = LeaveType::all();
        return view('leave-types.index', compact('title', 'leaveTypes'));
    }

    public function create()
    {
        Gate::authorize('manage-leave-types');
        $title = 'Add Leave Type';
        return view('leave-types.create', compact('title'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-leave-types');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'max_days' => 'required|integer|min:0',
            'is_carry_forward' => 'boolean'
        ]);
        
        $validated['is_carry_forward'] = $request->has('is_carry_forward');

        LeaveType::create($validated);

        return redirect()->route('leave-types.index')->with('success', 'Leave Type created successfully.');
    }

    public function edit(LeaveType $leaveType)
    {
        Gate::authorize('manage-leave-types');
        $title = 'Edit Leave Type';
        return view('leave-types.edit', compact('title', 'leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        Gate::authorize('manage-leave-types');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'max_days' => 'required|integer|min:0',
            'is_carry_forward' => 'boolean'
        ]);

        $validated['is_carry_forward'] = $request->has('is_carry_forward');

        $leaveType->update($validated);

        return redirect()->route('leave-types.index')->with('success', 'Leave Type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        Gate::authorize('manage-leave-types');
        $leaveType->delete();
        return redirect()->route('leave-types.index')->with('success', 'Leave Type deleted successfully.');
    }
}
