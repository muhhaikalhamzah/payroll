<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Department::class);
        $departments = Department::with('parent')->get();
        $title = 'Departments';
        return view('master-data.departments.index', compact('departments', 'title'));
    }

    public function create()
    {
        Gate::authorize('create', Department::class);
        $title = 'Create Department';
        $departments = Department::all();
        return view('master-data.departments.create', compact('departments', 'title'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Department::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_department_id' => 'nullable|exists:departments,id',
        ]);

        Department::create($validated);
        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        Gate::authorize('update', $department);
        $title = 'Edit Department';
        $departments = Department::where('id', '!=', $department->id)->get(); // Prevent self-referencing in UI
        return view('master-data.departments.edit', compact('department', 'departments', 'title'));
    }

    public function update(Request $request, Department $department)
    {
        Gate::authorize('update', $department);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_department_id' => [
                'nullable',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($department) {
                    if ($value == $department->id) {
                        $fail('A department cannot be its own parent.');
                    }
                }
            ],
        ]);

        $department->update($validated);
        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        Gate::authorize('delete', $department);
        
        if ($department->children()->count() > 0 || $department->positions()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete department with active children or positions.');
        }
        
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}
