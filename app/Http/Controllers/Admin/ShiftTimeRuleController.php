<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftTimeRule;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftTimeRuleController extends Controller
{
    public function index(Request $request)
    {
        $shiftId = $request->get('shift_id');
        $query = ShiftTimeRule::with('shift')->orderBy('shift_id')->orderBy('day_of_week');
        if ($shiftId) {
            $query->where('shift_id', $shiftId);
        }
        $rules = $query->paginate(20)->appends($request->query());
        $shifts = Shift::orderBy('code')->get();
        return view('admin.shift_time_rules.index', compact('rules', 'shifts', 'shiftId'));
    }

    public function create()
    {
        $shifts = Shift::orderBy('code')->get();
        return view('admin.shift_time_rules.create', compact('shifts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        ShiftTimeRule::create($data);
        return redirect()->route('admin.shift_time_rules.index', ['shift_id' => $data['shift_id']]);
    }

    public function edit(ShiftTimeRule $shiftTimeRule)
    {
        $shifts = Shift::orderBy('code')->get();
        return view('admin.shift_time_rules.edit', ['rule' => $shiftTimeRule, 'shifts' => $shifts]);
    }

    public function update(Request $request, ShiftTimeRule $shiftTimeRule)
    {
        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $shiftTimeRule->update($data);
        return redirect()->route('admin.shift_time_rules.index', ['shift_id' => $data['shift_id']]);
    }

    public function destroy(ShiftTimeRule $shiftTimeRule)
    {
        $shiftId = $shiftTimeRule->shift_id;
        $shiftTimeRule->delete();
        return redirect()->route('admin.shift_time_rules.index', ['shift_id' => $shiftId]);
    }
}
