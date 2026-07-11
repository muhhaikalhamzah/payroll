<?php

namespace App\Http\Controllers;

use App\Models\SalaryStructure;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SalaryStructureController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', SalaryStructure::class);
        $salaryStructures = SalaryStructure::with('position')->get();
        $title = 'Salary Structures';
        return view('master-data.salary-structures.index', compact('salaryStructures', 'title'));
    }

    public function create()
    {
        Gate::authorize('create', SalaryStructure::class);
        $title = 'Create Salary Structure';
        $positions = Position::all();
        return view('master-data.salary-structures.create', compact('positions', 'title'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', SalaryStructure::class);
        
        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id|unique:salary_structures,position_id',
            'base_salary' => 'required|numeric|min:0',
        ]);

        SalaryStructure::create($validated);
        return redirect()->route('salary-structures.index')->with('success', 'Salary Structure created successfully.');
    }

    public function edit(SalaryStructure $salaryStructure)
    {
        Gate::authorize('update', $salaryStructure);
        $title = 'Edit Salary Structure';
        $positions = Position::all();
        return view('master-data.salary-structures.edit', compact('salaryStructure', 'positions', 'title'));
    }

    public function update(Request $request, SalaryStructure $salaryStructure)
    {
        Gate::authorize('update', $salaryStructure);

        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id|unique:salary_structures,position_id,' . $salaryStructure->id,
            'base_salary' => 'required|numeric|min:0',
        ]);

        $salaryStructure->update($validated);
        return redirect()->route('salary-structures.index')->with('success', 'Salary Structure updated successfully.');
    }

    public function destroy(SalaryStructure $salaryStructure)
    {
        Gate::authorize('delete', $salaryStructure);
        
        $salaryStructure->delete();
        return redirect()->route('salary-structures.index')->with('success', 'Salary Structure deleted successfully.');
    }
}
