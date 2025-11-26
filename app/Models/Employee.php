<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'person_code', 'wa_number', 'default_work_place_id', 'session_key', 'is_active'];

    public function defaultWorkPlace()
    {
        return $this->belongsTo(WorkPlace::class, 'default_work_place_id');
    }
}
