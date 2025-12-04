<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftWeekPattern;
use Illuminate\Support\Carbon;

class ShiftWeekPatternController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $pp1Id = Shift::where('code','PP1IPTI')->value('id');
        $pp2Id = Shift::where('code','PP2IPTI')->value('id');
        $sp1Id = Shift::where('code','SP1IPTI')->value('id');
        $sp2Id = Shift::where('code','SP2IPTI')->value('id');
        $patterns = ShiftWeekPattern::with(['employee','workPlace','items.shift'])->orderBy('employee_id')->paginate(15);
        $patterns->getCollection()->transform(function($p) use ($today,$pp1Id,$pp2Id,$sp1Id,$sp2Id){
            $start = $p->start_date->copy()->startOfDay();
            $diffWeeks = $start->diffInWeeks($today->copy()->startOfDay(), false);
            $cycle = max(1,(int)$p->cycle_length_weeks);
            $pos = $diffWeeks >= 0 ? ($diffWeeks % $cycle) : 0;
            $items = $p->items->sortBy('order_index')->values();
            $currentShiftId = null;
            $acc = 0;
            foreach ($items as $it) {
                if ($pos < ($acc + (int)$it->duration_weeks)) { $currentShiftId = $it->shift_id; break; }
                $acc += (int)$it->duration_weeks;
            }
            $day = $today->isoWeekday();
            $targetShiftId = $currentShiftId;
            $cur = $currentShiftId ? Shift::find($currentShiftId) : null;
            if ($cur && is_string($cur->code)) {
                $code = $cur->code;
                if (str_starts_with($code,'PP')) {
                    $targetShiftId = ($day===5 && $pp2Id) ? $pp2Id : ($pp1Id ?: $currentShiftId);
                } elseif (str_starts_with($code,'SP')) {
                    $targetShiftId = ($day===5 && $sp2Id) ? $sp2Id : ($sp1Id ?: $currentShiftId);
                }
            }
            $p->current_shift = $cur;
            $p->today_shift = $targetShiftId ? Shift::find($targetShiftId) : null;
            $p->kind = ($items->count() > 1 && $p->cycle_length_weeks == 6) ? '2 shift (3-3 minggu)' : '1 shift (pagi)';
            return $p;
        });
        return view('admin.shift_week_patterns.index', compact('patterns','today'));
    }
}

