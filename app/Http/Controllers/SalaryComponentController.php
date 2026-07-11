<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SalaryComponentController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', SalaryComponent::class);
        $salaryComponents = SalaryComponent::all();
        $title = 'Salary Components';
        return view('master-data.salary-components.index', compact('salaryComponents', 'title'));
    }

    public function create()
    {
        Gate::authorize('create', SalaryComponent::class);
        $title = 'Create Salary Component';
        return view('master-data.salary-components.create', compact('title'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', SalaryComponent::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:allowance,deduction',
            'is_fixed' => 'boolean',
            'is_taxable' => 'boolean',
        ]);

        $validated['is_fixed'] = $request->has('is_fixed');
        $validated['is_taxable'] = $request->has('is_taxable');

        SalaryComponent::create($validated);
        return redirect()->route('salary-components.index')->with('success', 'Salary Component created successfully.');
    }

    public function edit(SalaryComponent $salaryComponent)
    {
        Gate::authorize('update', $salaryComponent);
        $title = 'Edit Salary Component';
        return view('master-data.salary-components.edit', compact('salaryComponent', 'title'));
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        Gate::authorize('update', $salaryComponent);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:allowance,deduction',
            'is_fixed' => 'boolean',
            'is_taxable' => 'boolean',
        ]);

        $validated['is_fixed'] = $request->has('is_fixed');
        $validated['is_taxable'] = $request->has('is_taxable');

        $salaryComponent->update($validated);
        return redirect()->route('salary-components.index')->with('success', 'Salary Component updated successfully.');
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        Gate::authorize('delete', $salaryComponent);
        
        $salaryComponent->delete();
        return redirect()->route('salary-components.index')->with('success', 'Salary Component deleted successfully.');
    }
}
