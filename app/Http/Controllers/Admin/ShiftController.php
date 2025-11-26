<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::orderBy('code')->paginate(10);
        return view('admin.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:shifts,code',
            'name' => 'required|string|max:255',
            'random_before_minutes' => 'nullable|integer|min:0|max:120',
            'random_after_minutes' => 'nullable|integer|min:0|max:120',
            'is_active' => 'nullable|boolean',
        ]);
        $data['random_before_minutes'] = (int) ($data['random_before_minutes'] ?? config('attendance.random_before_default'));
        $data['random_after_minutes'] = (int) ($data['random_after_minutes'] ?? config('attendance.random_after_default'));
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        Shift::create($data);
        return redirect()->route('admin.shifts.index');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:shifts,code,'.$shift->id,
            'name' => 'required|string|max:255',
            'random_before_minutes' => 'nullable|integer|min:0|max:120',
            'random_after_minutes' => 'nullable|integer|min:0|max:120',
            'is_active' => 'nullable|boolean',
        ]);
        $data['random_before_minutes'] = (int) ($data['random_before_minutes'] ?? $shift->random_before_minutes);
        $data['random_after_minutes'] = (int) ($data['random_after_minutes'] ?? $shift->random_after_minutes);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $shift->update($data);
        return redirect()->route('admin.shifts.index');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->route('admin.shifts.index');
    }
}
