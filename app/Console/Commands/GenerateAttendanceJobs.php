<?php
namespace App\Console\Commands;

use App\Models\AttendanceJob;
use App\Models\EmployeeShiftSchedule;
use App\Models\ShiftTimeRule;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateAttendanceJobs extends Command
{
    protected $signature = 'attendance:generate-jobs';
    protected $description = 'Generate attendance jobs for today based on schedules and shift rules';

    public function handle(): int
    {
        $today = Carbon::today();
        $day = $today->isoWeekday();
        $schedules = EmployeeShiftSchedule::with(['employee', 'workPlace', 'shift'])->whereDate('date', $today)->get();
        foreach ($schedules as $schedule) {
            $rule = ShiftTimeRule::where('shift_id', $schedule->shift_id)->where('day_of_week', $day)->where('is_active', true)->first();
            if (!$rule) {
                continue;
            }
            $start = Carbon::parse($today->toDateString().' '.$rule->start_time);
            $end = Carbon::parse($today->toDateString().' '.$rule->end_time);
            $loginStart = $start->copy()->subMinutes(19);
            $loginEnd = $start->copy()->subMinutes(9);
            $logoutStart = $end->copy()->addMinutes(11);
            $logoutEnd = $end->copy()->addMinutes(21);
            $loginFrom = $loginStart->copy()->subDays(7)->startOfDay();
            $loginTo = $loginEnd->copy()->addDays(7)->endOfDay();
            $usedLoginMinutes = AttendanceJob::where('employee_id',$schedule->employee_id)
                ->whereBetween('run_at', [$loginFrom, $loginTo])
                ->where('type','login')
                ->pluck('run_at')
                ->map(fn($dt) => \Illuminate\Support\Carbon::parse($dt)->format('i'))
                ->all();
            $usedLoginMmss = AttendanceJob::where('employee_id',$schedule->employee_id)
                ->whereBetween('run_at', [$loginFrom, $loginTo])
                ->where('type','login')
                ->pluck('run_at')
                ->map(fn($dt) => \Illuminate\Support\Carbon::parse($dt)->format('i:s'))
                ->all();
            $loginSalt = $schedule->employee_id.'|login|'.$today->toDateString();
            $loginRunAt = $this->randomUniqueMinuteAndSecond($loginStart, $loginEnd, $usedLoginMinutes, $usedLoginMmss, $loginSalt);

            $logoutFrom = $logoutStart->copy()->subDays(7)->startOfDay();
            $logoutTo = $logoutEnd->copy()->addDays(7)->endOfDay();
            $usedLogoutMinutes = AttendanceJob::where('employee_id',$schedule->employee_id)
                ->whereBetween('run_at', [$logoutFrom, $logoutTo])
                ->where('type','logout')
                ->pluck('run_at')
                ->map(fn($dt) => \Illuminate\Support\Carbon::parse($dt)->format('i'))
                ->all();
            $usedLogoutMmss = AttendanceJob::where('employee_id',$schedule->employee_id)
                ->whereBetween('run_at', [$logoutFrom, $logoutTo])
                ->where('type','logout')
                ->pluck('run_at')
                ->map(fn($dt) => \Illuminate\Support\Carbon::parse($dt)->format('i:s'))
                ->all();
            $logoutSalt = $schedule->employee_id.'|logout|'.$today->toDateString();
            $logoutRunAt = $this->randomUniqueMinuteAndSecond($logoutStart, $logoutEnd, $usedLogoutMinutes, $usedLogoutMmss, $logoutSalt);
            if ($loginRunAt->second === 0) { $loginRunAt = $loginRunAt->copy()->setSecond(random_int(1,59)); }
            if ($logoutRunAt->second === 0) { $logoutRunAt = $logoutRunAt->copy()->setSecond(random_int(1,59)); }
            $loginMsg = $schedule->login_message ?: ('log#in#'.$schedule->workPlace->code.'#'.$schedule->employee->person_code);
            $logoutMsg = $schedule->logout_message ?: ('log#out#'.$schedule->workPlace->code.'#'.$schedule->employee->person_code);
            $loginJob = AttendanceJob::where('employee_id',$schedule->employee_id)
                ->whereDate('date',$today->toDateString())
                ->where('type','login')->first();
            if ($loginJob) {
                $loginJob->run_at = $loginRunAt;
                $loginJob->message = $loginMsg;
                $loginJob->save();
            } else {
                AttendanceJob::create([
                    'employee_id' => $schedule->employee_id,
                    'work_place_id' => $schedule->work_place_id,
                    'shift_id' => $schedule->shift_id,
                    'date' => $today->toDateString(),
                    'type' => 'login',
                    'message' => $loginMsg,
                    'run_at' => $loginRunAt,
                    'status' => 'pending',
                ]);
            }
            $logoutJob = AttendanceJob::where('employee_id',$schedule->employee_id)
                ->whereDate('date',$today->toDateString())
                ->where('type','logout')->first();
            if ($logoutJob) {
                $logoutJob->run_at = $logoutRunAt;
                $logoutJob->message = $logoutMsg;
                $logoutJob->save();
            } else {
                AttendanceJob::create([
                    'employee_id' => $schedule->employee_id,
                    'work_place_id' => $schedule->work_place_id,
                    'shift_id' => $schedule->shift_id,
                    'date' => $today->toDateString(),
                    'type' => 'logout',
                    'message' => $logoutMsg,
                    'run_at' => $logoutRunAt,
                    'status' => 'pending',
                ]);
            }
        }
        return self::SUCCESS;
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
}
