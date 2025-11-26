<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShiftSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'work_place_id', 'shift_id', 'date', 'login_message', 'logout_message'];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function workPlace()
    {
        return $this->belongsTo(WorkPlace::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
