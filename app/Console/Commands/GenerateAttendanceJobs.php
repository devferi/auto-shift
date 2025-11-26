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
            $loginRunAt = $this->randomUniqueTimeBetween($loginStart, $loginEnd);
            $logoutRunAt = $this->randomUniqueTimeBetween($logoutStart, $logoutEnd);
            $loginMsg = $schedule->login_message ?: ('log#in#'.$schedule->workPlace->code.'#'.$schedule->employee->person_code);
            $logoutMsg = $schedule->logout_message ?: ('log#out#'.$schedule->workPlace->code.'#'.$schedule->employee->person_code);
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
        return self::SUCCESS;
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
            ->map(fn($dt) => \Illuminate\Support\Carbon::parse($dt)->format('i:s'))
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
}
