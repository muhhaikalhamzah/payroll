<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PositionController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Position::class);
        $positions = Position::with('department')->get();
        $title = 'Positions';
        return view('master-data.positions.index', compact('positions', 'title'));
    }

    public function create()
    {
        Gate::authorize('create', Position::class);
        $title = 'Create Position';
        $departments = Department::all();
        return view('master-data.positions.create', compact('departments', 'title'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Position::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        Position::create($validated);
        return redirect()->route('positions.index')->with('success', 'Position created successfully.');
    }

    public function edit(Position $position)
    {
        Gate::authorize('update', $position);
        $title = 'Edit Position';
        $departments = Department::all();
        return view('master-data.positions.edit', compact('position', 'departments', 'title'));
    }

    public function update(Request $request, Position $position)
    {
        Gate::authorize('update', $position);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        $position->update($validated);
        return redirect()->route('positions.index')->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        Gate::authorize('delete', $position);
        
        try {
            $position->delete();
            return redirect()->route('positions.index')->with('success', 'Position deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'Cannot delete position. It is likely still in use.');
        }
    }
}
