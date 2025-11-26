<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\WorkPlace;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('defaultWorkPlace')->orderBy('name')->paginate(10);
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $workPlaces = WorkPlace::orderBy('name')->get();
        return view('admin.employees.create', compact('workPlaces'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'person_code' => 'required|string|max:50|unique:employees,person_code',
            'wa_number' => 'required|string|max:20',
            'default_work_place_id' => 'nullable|exists:work_places,id',
            'session_key' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        Employee::create($data);
        return redirect()->route('admin.employees.index');
    }

    public function edit(Employee $employee)
    {
        $workPlaces = WorkPlace::orderBy('name')->get();
        return view('admin.employees.edit', compact('employee', 'workPlaces'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'person_code' => 'required|string|max:50|unique:employees,person_code,'.$employee->id,
            'wa_number' => 'required|string|max:20',
            'default_work_place_id' => 'nullable|exists:work_places,id',
            'session_key' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $employee->update($data);
        return redirect()->route('admin.employees.index');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('admin.employees.index');
    }
}
