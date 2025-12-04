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

    public function monthly(Request $request)
    {
        $month = $request->get('month');
        if (!$month) { $month = now()->format('Y-m'); }
        try { $start = \Illuminate\Support\Carbon::parse($month.'-01')->startOfMonth(); } catch (\Throwable $e) { $start = now()->startOfMonth(); }
        $end = $start->copy()->endOfMonth();
        $days = (int) $start->daysInMonth;
        $employees = Employee::orderBy('name')->get();
        $schedules = EmployeeShiftSchedule::with(['shift'])
            ->whereDate('date','>=',$start->toDateString())
            ->whereDate('date','<=',$end->toDateString())
            ->get();
        $grid = [];
        foreach ($schedules as $sc) {
            $d = (int) \Illuminate\Support\Carbon::parse($sc->date)->day;
            $grid[$sc->employee_id][$d] = $sc;
        }
        // Reorder employees to follow Excel order if available
        $excelPath = base_path('Jadwal Inpresso Programmer Desember.xlsx');
        $excelOrder = $this->readExcelNames($excelPath);
        if (!empty($excelOrder)) {
            $orderMap = array_flip($excelOrder);
            $employees = $employees->sortBy(function($e) use ($orderMap) {
                return isset($orderMap[$e->name]) ? $orderMap[$e->name] : (100000 + $e->id);
            })->values();
        }
        return view('admin.schedules.monthly', compact('start','end','days','employees','grid'));
    }

    protected function readExcelNames(string $path): array
    {
        if (!is_file($path)) { return []; }
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) { return []; }
        $shared = [];
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml) {
            $sx = @simplexml_load_string($xml);
            if ($sx) {
                foreach ($sx->si as $si) {
                    $text = '';
                    foreach ($si->t as $t) { $text .= (string) $t; }
                    if ($text === '' && isset($si->r)) {
                        foreach ($si->r as $r) { $text .= (string) ($r->t ?? ''); }
                    }
                    $shared[] = $text;
                }
            }
        }
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (!$sheet) { return []; }
        $sx = @simplexml_load_string($sheet);
        if (!$sx) { return []; }
        $names = [];
        foreach ($sx->sheetData->row as $row) {
            $rIdx = (int) $row['r'];
            if ($rIdx <= 1) { continue; } // skip header
            $name = '';
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                if (preg_match('/^A[0-9]+$/', $ref)) {
                    $val = (string) ($c->v ?? '');
                    $type = (string) ($c['t'] ?? '');
                    if ($type === 's') {
                        $ix = (int) $val;
                        $name = $shared[$ix] ?? '';
                    } else {
                        $name = $val;
                    }
                    break;
                }
            }
            $name = trim($name);
            if ($name !== '') { $names[] = $name; }
        }
        return $names;
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
