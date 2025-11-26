<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceJob;
use App\Models\Employee;
use App\Models\WorkPlace;
use App\Models\Shift;
use App\Models\EmployeeShiftSchedule;
use App\Models\ShiftTimeRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\WhatsappApiLog;

class AttendanceJobController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceJob::with(['employee','workPlace','shift'])->orderBy('run_at','desc');
        foreach (['status','type','employee_id','work_place_id','shift_id'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->get($f));
            }
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->get('date'));
        }
        if ($request->filled('start')) {
            $query->where('run_at','>=',$request->get('start'));
        }
        if ($request->filled('end')) {
            $query->where('run_at','<=',$request->get('end'));
        }
        $jobs = $query->paginate(20)->appends($request->query());
        $employees = Employee::orderBy('name')->get();
        $workPlaces = WorkPlace::orderBy('name')->get();
        $shifts = Shift::orderBy('code')->get();
        return view('admin.jobs.index', compact('jobs','employees','workPlaces','shifts'));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        $workPlaces = WorkPlace::orderBy('name')->get();
        $shifts = Shift::orderBy('code')->get();
        return view('admin.jobs.create', compact('employees','workPlaces','shifts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_place_id' => 'required|exists:work_places,id',
            'shift_id' => 'required|exists:shifts,id',
            'date' => 'required|date',
            'type' => 'required|in:login,logout',
            'message' => 'required|string',
            'run_at' => 'required|date',
            'status' => 'nullable|in:pending,done,failed',
        ]);
        $data['status'] = $data['status'] ?? 'pending';
        AttendanceJob::create($data);
        return redirect()->route('admin.attendance_jobs.index');
    }

    public function edit(AttendanceJob $attendance_job)
    {
        $employees = Employee::orderBy('name')->get();
        $workPlaces = WorkPlace::orderBy('name')->get();
        $shifts = Shift::orderBy('code')->get();
        return view('admin.jobs.edit', ['job' => $attendance_job, 'employees' => $employees, 'workPlaces' => $workPlaces, 'shifts' => $shifts]);
    }

    public function update(Request $request, AttendanceJob $attendance_job)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_place_id' => 'required|exists:work_places,id',
            'shift_id' => 'required|exists:shifts,id',
            'date' => 'required|date',
            'type' => 'required|in:login,logout',
            'message' => 'required|string',
            'run_at' => 'required|date',
            'status' => 'required|in:pending,done,failed',
            'attempts' => 'nullable|integer|min:0',
            'api_url' => 'nullable|string',
            'api_response' => 'nullable|string',
            'api_request_body' => 'nullable|string',
        ]);
        $attendance_job->update($data);
        return redirect()->route('admin.attendance_jobs.index');
    }

    public function destroy(AttendanceJob $attendance_job)
    {
        $attendance_job->delete();
        return redirect()->route('admin.attendance_jobs.index');
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'date' => ['required','date'],
        ]);
        $date = Carbon::parse($data['date']);
        $dow = $date->dayOfWeek;
        $schedules = EmployeeShiftSchedule::with(['employee','workPlace','shift'])
            ->whereDate('date', $date->toDateString())
            ->get();
        $created = 0;
        foreach ($schedules as $sch) {
            $rule = ShiftTimeRule::where('shift_id', $sch->shift_id)
                ->where('day_of_week', $dow)
                ->where('is_active', true)
                ->first();
            if (!$rule) { continue; }
            $startAt = Carbon::parse($date->toDateString().' '.$rule->start_time);
            $endAt = Carbon::parse($date->toDateString().' '.$rule->end_time);
            $loginStart = $startAt->copy()->subMinutes(19);
            $loginEnd = $startAt->copy()->subMinutes(9);
            $logoutStart = $endAt->copy()->addMinutes(11);
            $logoutEnd = $endAt->copy()->addMinutes(21);
            $loginRunAt = $this->randomUniqueTimeBetween($loginStart, $loginEnd);
            $logoutRunAt = $this->randomUniqueTimeBetween($logoutStart, $logoutEnd);
            $loginMsg = $sch->login_message ?: ('log#in#'.$sch->workPlace->code.'#'.$sch->employee->person_code);
            $logoutMsg = $sch->logout_message ?: ('log#out#'.$sch->workPlace->code.'#'.$sch->employee->person_code);
            $existsLogin = AttendanceJob::where('employee_id',$sch->employee_id)
                ->whereDate('date',$date->toDateString())
                ->where('type','login')->exists();
            if (!$existsLogin) {
                AttendanceJob::create([
                    'employee_id' => $sch->employee_id,
                    'work_place_id' => $sch->work_place_id,
                    'shift_id' => $sch->shift_id,
                    'date' => $date->toDateString(),
                    'type' => 'login',
                    'message' => $loginMsg,
                    'run_at' => $loginRunAt,
                    'status' => 'pending',
                ]);
                $created++;
            }
            $existsLogout = AttendanceJob::where('employee_id',$sch->employee_id)
                ->whereDate('date',$date->toDateString())
                ->where('type','logout')->exists();
            if (!$existsLogout) {
                AttendanceJob::create([
                    'employee_id' => $sch->employee_id,
                    'work_place_id' => $sch->work_place_id,
                    'shift_id' => $sch->shift_id,
                    'date' => $date->toDateString(),
                    'type' => 'logout',
                    'message' => $logoutMsg,
                    'run_at' => $logoutRunAt,
                    'status' => 'pending',
                ]);
                $created++;
            }
        }
        return redirect()->route('admin.attendance_jobs.index', ['date' => $date->toDateString()])
            ->with('status', 'Generated '.$created.' jobs');
    }

    protected function randomUniqueTimeBetween(Carbon $start, Carbon $end): Carbon
    {
        if ($end->lessThanOrEqualTo($start)) {
            $end = $start->copy()->addMinutes(2);
        }
        $diffMinutes = $end->diffInMinutes($start);
        if ($diffMinutes <= 0) {
            return $start->copy()->addMinute();
        }
        $from = $start->copy()->subDays(7)->startOfDay();
        $to = $start->copy()->addDays(7)->endOfDay();
        $used = AttendanceJob::whereBetween('run_at', [$from, $to])
            ->pluck('run_at')
            ->map(fn($dt) => Carbon::parse($dt)->format('i:s'))
            ->all();
        $usedSet = array_flip($used);
        for ($tries = 0; $tries < 200; $tries++) {
            $minuteOffset = $diffMinutes > 1 ? random_int(1, $diffMinutes - 1) : 1;
            $secondOffset = random_int(1, 59);
            $candidate = $start->copy()->addMinutes($minuteOffset);
            $mmss = $candidate->format('i').':'.sprintf('%02d', $secondOffset);
            if (!isset($usedSet[$mmss])) {
                return $candidate->copy()->addSeconds($secondOffset);
            }
        }
        $minuteOffset = $diffMinutes > 1 ? random_int(1, $diffMinutes - 1) : 1;
        $secondOffset = random_int(1, 59);
        return $start->copy()->addMinutes($minuteOffset)->addSeconds($secondOffset);
    }
    public function run(Request $request)
    {
        Artisan::call('attendance:run-jobs');
        return redirect()->route('admin.attendance_jobs.index')->with('status', 'Executed pending jobs');
    }

    public function logs(Request $request)
    {
        $query = AttendanceJob::orderBy('run_at','desc');
        if ($request->filled('status')) { $query->where('status', $request->get('status')); }
        if ($request->filled('date')) { $query->whereDate('date', $request->get('date')); }
        $jobs = $query->paginate(20)->appends($request->query());
        $apiLogs = WhatsappApiLog::orderBy('created_at','desc')->paginate(20);
        return view('admin.logs.whatsapp', compact('jobs','apiLogs'));
    }
}
