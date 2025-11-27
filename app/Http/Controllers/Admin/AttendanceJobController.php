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
            $loginFrom = $loginStart->copy()->subDays(7)->startOfDay();
            $loginTo = $loginEnd->copy()->addDays(7)->endOfDay();
            $usedLoginMinutes = AttendanceJob::where('employee_id',$sch->employee_id)
                ->whereBetween('run_at', [$loginFrom, $loginTo])
                ->where('type','login')
                ->pluck('run_at')
                ->map(fn($dt) => Carbon::parse($dt)->format('i'))
                ->all();
            $usedLoginMmss = AttendanceJob::where('employee_id',$sch->employee_id)
                ->whereBetween('run_at', [$loginFrom, $loginTo])
                ->where('type','login')
                ->pluck('run_at')
                ->map(fn($dt) => Carbon::parse($dt)->format('i:s'))
                ->all();
            $loginSalt = $sch->employee_id.'|login|'.$date->toDateString();
            $loginRunAt = $this->randomUniqueMinuteAndSecond($loginStart, $loginEnd, $usedLoginMinutes, $usedLoginMmss, $loginSalt);

            $logoutFrom = $logoutStart->copy()->subDays(7)->startOfDay();
            $logoutTo = $logoutEnd->copy()->addDays(7)->endOfDay();
            $usedLogoutMinutes = AttendanceJob::where('employee_id',$sch->employee_id)
                ->whereBetween('run_at', [$logoutFrom, $logoutTo])
                ->where('type','logout')
                ->pluck('run_at')
                ->map(fn($dt) => Carbon::parse($dt)->format('i'))
                ->all();
            $usedLogoutMmss = AttendanceJob::where('employee_id',$sch->employee_id)
                ->whereBetween('run_at', [$logoutFrom, $logoutTo])
                ->where('type','logout')
                ->pluck('run_at')
                ->map(fn($dt) => Carbon::parse($dt)->format('i:s'))
                ->all();
            $logoutSalt = $sch->employee_id.'|logout|'.$date->toDateString();
            $logoutRunAt = $this->randomUniqueMinuteAndSecond($logoutStart, $logoutEnd, $usedLogoutMinutes, $usedLogoutMmss, $logoutSalt);
            if ($loginRunAt->second === 0) { $loginRunAt = $loginRunAt->copy()->setSecond(random_int(1,59)); }
            if ($logoutRunAt->second === 0) { $logoutRunAt = $logoutRunAt->copy()->setSecond(random_int(1,59)); }
            $loginMsg = $sch->login_message ?: ('log#in#'.$sch->workPlace->code.'#'.$sch->employee->person_code);
            $logoutMsg = $sch->logout_message ?: ('log#out#'.$sch->workPlace->code.'#'.$sch->employee->person_code);
            $existingLogin = AttendanceJob::where('employee_id',$sch->employee_id)
                ->whereDate('date',$date->toDateString())
                ->where('type','login')->first();
            if ($existingLogin) {
                $existingLogin->run_at = $loginRunAt;
                $existingLogin->message = $loginMsg;
                $existingLogin->save();
                $created++;
            } else {
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
            $existingLogout = AttendanceJob::where('employee_id',$sch->employee_id)
                ->whereDate('date',$date->toDateString())
                ->where('type','logout')->first();
            if ($existingLogout) {
                $existingLogout->run_at = $logoutRunAt;
                $existingLogout->message = $logoutMsg;
                $existingLogout->save();
                $created++;
            } else {
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

    protected function randomUniqueMinuteAndSecond(Carbon $start, Carbon $end, array $excludeMinutes, array $excludeMmss, ?string $salt = null): Carbon
    {
        if ($end->lessThanOrEqualTo($start)) {
            $end = $start->copy()->addMinutes(2);
        }
        $diffMinutes = $end->diffInMinutes($start);
        if ($diffMinutes <= 0) {
            return $start->copy()->addMinute()->setSecond(random_int(1,59));
        }
        $excludeMinuteSet = array_flip($excludeMinutes);
        $excludeMmssSet = array_flip($excludeMmss);
        $offsetPool = [];
        for ($i = 1; $i < $diffMinutes; $i++) {
            $m = $start->copy()->addMinutes($i)->format('i');
            if (!isset($excludeMinuteSet[$m])) {
                $offsetPool[] = $i;
            }
        }
        $indices = !empty($offsetPool) ? array_values($offsetPool) : range(1, max(1, $diffMinutes - 1));
        $count = count($indices);
        if ($count === 0) { $indices = [1]; $count = 1; }
        $saltParts = explode('|', (string) $salt);
        $empId = isset($saltParts[0]) ? (int) $saltParts[0] : 0;
        $typeKey = isset($saltParts[1]) && $saltParts[1] === 'logout' ? 11 : 3;
        $baseOffset = 1 + ((($start->dayOfYear + ($empId * 7) + $typeKey)) % max(1, ($diffMinutes - 1)));
        // Find baseOffset in indices or step forward until found
        $startIdx = array_search($baseOffset, $indices, true);
        if ($startIdx === false) { $startIdx = 0; }
        for ($step = 0; $step < $count; $step++) {
            $i = $indices[($startIdx + $step) % $count];
            $candidate = $start->copy()->addMinutes($i);
            $secSeed = ($start->dayOfYear + crc32(($salt ?? '').'|'.$candidate->format('YmdHi')));
            $secondOffset = 1 + ($secSeed % 59);
            $mmss = $candidate->format('i').':'.sprintf('%02d', $secondOffset);
            if (!isset($excludeMmssSet[$mmss])) {
                return $candidate->copy()->setSecond($secondOffset);
            }
        }
        $candidate = $start->copy()->addMinutes($indices[0]);
        $secondOffset = 1 + (($start->dayOfYear + crc32(($salt ?? '').'|fb|'.$candidate->format('YmdHi'))) % 59);
        return $candidate->copy()->setSecond($secondOffset);
    }
    public function run(Request $request)
    {
        Artisan::call('attendance:run-jobs');
        return redirect()->route('admin.attendance_jobs.index')->with('status', 'Executed pending jobs');
    }

    public function randomizeSeconds(Request $request)
    {
        $query = AttendanceJob::where('status','pending');
        if ($request->filled('date')) {
            $query->whereDate('date', $request->get('date'));
        }
        $jobs = $query->orderBy('run_at')->limit(500)->get();
        $updated = 0;
        foreach ($jobs as $job) {
            $ra = Carbon::parse($job->run_at);
            if ($ra->second !== 0) { continue; }
            $from = $ra->copy()->subDays(7)->startOfDay();
            $to = $ra->copy()->addDays(7)->endOfDay();
            $used = AttendanceJob::whereBetween('run_at', [$from, $to])
                ->pluck('run_at')
                ->map(fn($dt) => Carbon::parse($dt)->format('i:s'))
                ->all();
            $usedSet = array_flip($used);
            for ($tries = 0; $tries < 200; $tries++) {
                $sec = random_int(1, 59);
                $mmss = $ra->format('i').':'.sprintf('%02d', $sec);
                if (!isset($usedSet[$mmss])) {
                    $job->run_at = $ra->copy()->setSecond($sec);
                    $job->save();
                    $updated++;
                    break;
                }
            }
        }
        return redirect()->route('admin.attendance_jobs.index', $request->only(['date']))->with('status', 'Randomized seconds for '.$updated.' jobs');
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
