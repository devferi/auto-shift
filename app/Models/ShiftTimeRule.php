<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftTimeRule extends Model
{
    use HasFactory;

    protected $fillable = ['shift_id', 'day_of_week', 'start_time', 'end_time', 'is_active'];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
