<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceJob extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'work_place_id', 'shift_id', 'date', 'type', 'message', 'run_at', 'status', 'api_url', 'api_response', 'attempts'];

    protected $casts = [
        'date' => 'date',
        'run_at' => 'datetime',
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
