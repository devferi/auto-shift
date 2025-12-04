<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\ShiftWeekPattern;
use App\Models\ShiftWeekPatternItem;
use App\Models\WorkPlace;

class ShiftWeekPatternSeeder extends Seeder
{
    public function run(): void
    {
        $pp1 = Shift::firstOrCreate(
            ['code' => 'PP1IPTI'],
            ['name' => 'Pagi Senin - Kamis', 'random_before_minutes' => 15, 'random_after_minutes' => 15, 'is_active' => true]
        );
        $sp1 = Shift::firstOrCreate(
            ['code' => 'SP1IPTI'],
            ['name' => 'Siang Senin - Kamis', 'random_before_minutes' => 15, 'random_after_minutes' => 15, 'is_active' => true]
        );
        $defaultWp = WorkPlace::firstOrCreate(
            ['code' => 'OFFICE'],
            ['name' => 'Kantor', 'description' => null, 'is_active' => true]
        );
        $oneShiftNames = [
            'Ahmad Hatta',
            'Armeilya Rahmanis',
            'Rebeca Septina',
        ];
        $twoShiftNames = [
            'Andi Novan P.',
            'Ahmad Galang Satria',
            'Muhammad Axl Vyo Y',
            'Gagah Arif Legowo',
            'Feri Afrianto',
            'Edo Satrio',
            'Guntur Gedhe Mukti',
            'Ahmad Khoirul Anam',
            'Yuanita Hendra',
            'Ranu Krisna Afandhi',
        ];
        $twoShiftIds = Employee::whereIn('name', $twoShiftNames)->pluck('id')->all();
        $oneShiftIds = Employee::whereIn('name', $oneShiftNames)->pluck('id')->all();
        $start = Carbon::now()->startOfWeek();
        foreach ($twoShiftIds as $empId) {
            $workPlaceId = optional(Employee::find($empId)->defaultWorkPlace)->id ?: $defaultWp->id;
            $pattern = ShiftWeekPattern::firstOrCreate(
                ['employee_id' => $empId, 'start_date' => $start->toDateString()],
                ['work_place_id' => $workPlaceId, 'cycle_length_weeks' => 6, 'description' => '2-shift 3-3 weeks', 'is_active' => true]
            );
            ShiftWeekPatternItem::updateOrCreate(
                ['shift_week_pattern_id' => $pattern->id, 'order_index' => 1],
                ['duration_weeks' => 3, 'shift_id' => $pp1->id]
            );
            ShiftWeekPatternItem::updateOrCreate(
                ['shift_week_pattern_id' => $pattern->id, 'order_index' => 2],
                ['duration_weeks' => 3, 'shift_id' => $sp1->id]
            );
        }
        foreach ($oneShiftIds as $empId) {
            $workPlaceId = optional(Employee::find($empId)->defaultWorkPlace)->id ?: $defaultWp->id;
            $pattern = ShiftWeekPattern::firstOrCreate(
                ['employee_id' => $empId, 'start_date' => $start->toDateString()],
                ['work_place_id' => $workPlaceId, 'cycle_length_weeks' => 1, 'description' => '1-shift pagi', 'is_active' => true]
            );
            ShiftWeekPatternItem::updateOrCreate(
                ['shift_week_pattern_id' => $pattern->id, 'order_index' => 1],
                ['duration_weeks' => 1, 'shift_id' => $pp1->id]
            );
        }
    }
}
