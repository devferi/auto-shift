<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShiftSchedule;
use App\Models\Employee;
use App\Models\WorkPlace;
use App\Models\Shift;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeShiftSchedule::with(['employee','workPlace','shift'])->orderBy('date','desc');
        if ($request->filled('start')) {
            $query->whereDate('date','>=',$request->get('start'));
        }
        if ($request->filled('end')) {
            $query->whereDate('date','<=',$request->get('end'));
        }
        foreach (['employee_id','work_place_id','shift_id'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->get($f));
            }
        }
        $schedules = $query->paginate(20)->appends($request->query());
        $employees = Employee::orderBy('name')->get();
        $workPlaces = WorkPlace::orderBy('name')->get();
        $shifts = Shift::orderBy('code')->get();
        return view('admin.schedules.index', compact('schedules','employees','workPlaces','shifts'));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        $workPlaces = WorkPlace::orderBy('name')->get();
        $shifts = Shift::orderBy('code')->get();
        return view('admin.schedules.create', compact('employees','workPlaces','shifts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_place_id' => 'required|exists:work_places,id',
            'shift_id' => 'required|exists:shifts,id',
            'date' => 'required|date',
            'login_message' => 'nullable|string',
            'logout_message' => 'nullable|string',
        ]);
        EmployeeShiftSchedule::updateOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'date' => $data['date'],
            ],
            [
                'work_place_id' => $data['work_place_id'],
                'shift_id' => $data['shift_id'],
                'login_message' => $data['login_message'] ?? null,
                'logout_message' => $data['logout_message'] ?? null,
            ]
        );
        return redirect()->route('admin.schedules.index');
    }

    public function edit(EmployeeShiftSchedule $schedule)
    {
        $employees = Employee::orderBy('name')->get();
        $workPlaces = WorkPlace::orderBy('name')->get();
        $shifts = Shift::orderBy('code')->get();
        return view('admin.schedules.edit', compact('schedule','employees','workPlaces','shifts'));
    }

    public function show(EmployeeShiftSchedule $schedule)
    {
        return redirect()->route('admin.schedules.edit', $schedule);
    }

    public function update(Request $request, EmployeeShiftSchedule $schedule)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_place_id' => 'required|exists:work_places,id',
            'shift_id' => 'required|exists:shifts,id',
            'date' => 'required|date',
            'login_message' => 'nullable|string',
            'logout_message' => 'nullable|string',
        ]);
        $schedule->update($data);
        return redirect()->route('admin.schedules.index');
    }

    public function destroy(EmployeeShiftSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('admin.schedules.index');
    }
}
