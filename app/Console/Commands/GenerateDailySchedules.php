<?php
namespace App\Console\Commands;

use App\Models\EmployeeShiftSchedule;
use App\Models\ShiftWeekPattern;
use App\Models\Shift;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateDailySchedules extends Command
{
    protected $signature = 'attendance:generate-schedules {date?} {--month=}';
    protected $description = 'Generate daily employee shift schedules from weekly patterns';

    public function handle(): int
    {
        $argDate = $this->argument('date');
        $optMonth = $this->option('month');
        if ($optMonth) {
            try { $start = Carbon::parse($optMonth.'-01')->startOfMonth(); } catch (\Throwable $e) { $start = Carbon::now()->startOfMonth(); }
            $end = $start->copy()->endOfMonth();
            $cursor = $start->copy();
            while ($cursor->lessThanOrEqualTo($end)) {
                if (in_array($cursor->isoWeekday(), [6,7], true)) {
                    EmployeeShiftSchedule::whereDate('date', $cursor->toDateString())->delete();
                } else {
                    $this->generateForDate($cursor);
                }
                $cursor->addDay();
            }
            return self::SUCCESS;
        }
        $today = $argDate ? Carbon::parse($argDate)->startOfDay() : Carbon::today();
        $this->generateForDate($today);
        return self::SUCCESS;
    }

    protected function generateForDate(Carbon $today): void
    {
        $day = $today->isoWeekday();
        if (in_array($day, [6,7], true)) {
            EmployeeShiftSchedule::whereDate('date', $today->toDateString())->delete();
            return;
        }
        $pp1Id = Shift::where('code','PP1IPTI')->value('id');
        $pp2Id = Shift::where('code','PP2IPTI')->value('id');
        $sp1Id = Shift::where('code','SP1IPTI')->value('id');
        $sp2Id = Shift::where('code','SP2IPTI')->value('id');
        $patterns = ShiftWeekPattern::where('is_active', true)->with(['items', 'employee.defaultWorkPlace'])->get();
        foreach ($patterns as $pattern) {
            $start = Carbon::parse($pattern->start_date)->startOfDay();
            $diffWeeks = $start->diffInWeeks($today->copy()->startOfDay(), false);
            if ($diffWeeks < 0) {
                continue;
            }
            $cycle = max(1, (int) $pattern->cycle_length_weeks);
            $position = $diffWeeks % $cycle;
            $items = $pattern->items->sortBy('order_index')->values();
            $currentShiftId = null;
            $acc = 0;
            foreach ($items as $item) {
                if ($position < ($acc + (int) $item->duration_weeks)) {
                    $currentShiftId = $item->shift_id;
                    break;
                }
                $acc += (int) $item->duration_weeks;
            }
            if (!$currentShiftId) {
                continue;
            }
            $targetShiftId = $currentShiftId;
            $currentShift = $currentShiftId ? Shift::find($currentShiftId) : null;
            if ($currentShift && is_string($currentShift->code)) {
                $code = $currentShift->code;
                if (str_starts_with($code, 'PP')) {
                    if ($day === 5 && $pp2Id) { $targetShiftId = $pp2Id; }
                    elseif (in_array($day, [1,2,3,4], true) && $pp1Id) { $targetShiftId = $pp1Id; }
                } elseif (str_starts_with($code, 'SP')) {
                    if ($day === 5 && $sp2Id) { $targetShiftId = $sp2Id; }
                    elseif (in_array($day, [1,2,3,4], true) && $sp1Id) { $targetShiftId = $sp1Id; }
                }
            }
            $workPlaceId = $pattern->work_place_id ?: optional($pattern->employee->defaultWorkPlace)->id;
            if (!$workPlaceId) {
                continue;
            }
            $exists = EmployeeShiftSchedule::where('employee_id', $pattern->employee_id)
                ->whereDate('date', $today->toDateString())
                ->exists();
            if (!$exists) {
                EmployeeShiftSchedule::create([
                    'employee_id' => $pattern->employee_id,
                    'date' => $today->toDateString(),
                    'work_place_id' => $workPlaceId,
                    'shift_id' => $targetShiftId,
                ]);
            }
        }
        return;
    }
}
