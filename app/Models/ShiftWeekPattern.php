<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftWeekPattern extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'work_place_id', 'start_date', 'cycle_length_weeks', 'description', 'is_active'];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function workPlace()
    {
        return $this->belongsTo(WorkPlace::class);
    }

    public function items()
    {
        return $this->hasMany(ShiftWeekPatternItem::class);
    }
}
