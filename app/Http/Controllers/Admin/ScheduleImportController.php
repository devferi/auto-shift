<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use App\Models\WorkPlace;
use App\Models\Shift;
use App\Models\EmployeeShiftSchedule;

class ScheduleImportController extends Controller
{
    public function create()
    {
        return view('admin.schedules.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);
        $path = $request->file('file')->store('imports');
        $full = Storage::path($path);
        $handle = fopen($full, 'r');
        $header = fgetcsv($handle);
        $map = [];
        foreach ($header as $idx => $col) {
            $map[strtolower(trim($col))] = $idx;
        }
        $created = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $date = $row[$map['date']] ?? null;
            $personCode = $row[$map['person_code']] ?? null;
            $workPlaceCode = $row[$map['work_place_code']] ?? null;
            $shiftCode = $row[$map['shift_code']] ?? null;
            $loginMessage = $map['login_message'] ?? null ? ($row[$map['login_message']] ?? null) : null;
            $logoutMessage = $map['logout_message'] ?? null ? ($row[$map['logout_message']] ?? null) : null;
            if (!$date || !$personCode || !$workPlaceCode || !$shiftCode) {
                continue;
            }
            $employee = Employee::where('person_code', $personCode)->first();
            $workPlace = WorkPlace::where('code', $workPlaceCode)->first();
            $shift = Shift::where('code', $shiftCode)->first();
            if (!$employee || !$workPlace || !$shift) {
                continue;
            }
            EmployeeShiftSchedule::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $date,
                ],
                [
                    'work_place_id' => $workPlace->id,
                    'shift_id' => $shift->id,
                    'login_message' => $loginMessage,
                    'logout_message' => $logoutMessage,
                ]
            );
            $created++;
        }
        fclose($handle);
        return redirect()->route('admin.schedules.index')->with('status', 'Imported '.$created.' records');
    }
}
