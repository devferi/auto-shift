<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftWeekPatternItem extends Model
{
    use HasFactory;

    protected $fillable = ['shift_week_pattern_id', 'order_index', 'duration_weeks', 'shift_id'];

    public function pattern()
    {
        return $this->belongsTo(ShiftWeekPattern::class, 'shift_week_pattern_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
