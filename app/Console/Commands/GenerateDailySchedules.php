<?php
namespace App\Console\Commands;

use App\Models\EmployeeShiftSchedule;
use App\Models\ShiftWeekPattern;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateDailySchedules extends Command
{
    protected $signature = 'attendance:generate-schedules';
    protected $description = 'Generate daily employee shift schedules from weekly patterns';

    public function handle(): int
    {
        $today = Carbon::today();
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
            $workPlaceId = $pattern->work_place_id ?: optional($pattern->employee->defaultWorkPlace)->id;
            if (!$workPlaceId) {
                continue;
            }
            EmployeeShiftSchedule::updateOrCreate(
                [
                    'employee_id' => $pattern->employee_id,
                    'date' => $today->toDateString(),
                ],
                [
                    'work_place_id' => $workPlaceId,
                    'shift_id' => $currentShiftId,
                ]
            );
        }
        return self::SUCCESS;
    }
}
