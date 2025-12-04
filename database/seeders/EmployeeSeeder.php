<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Ahmad Galang Satria',
            'Muhammad Axl Vyo Y',
            'Gagah Arif Legowo',
            'Feri Afrianto',
            'Edo Satrio',
            'Guntur Gedhe Mukti',
            'Ahmad Khoirul Anam',
            'Yuanita Hendra',
            'Ranu Krisna Afandhi',
            'Andi Novan P.',
            'Ahmad Hatta',
            'Armeilya Rahmanis',
            'Rebeca Septina',
        ];
        foreach ($names as $i => $name) {
            $code = 'emp'.str_pad((string)($i+1), 3, '0', STR_PAD_LEFT);
            Employee::firstOrCreate(
                ['person_code' => $code],
                ['name' => $name, 'wa_number' => '6281', 'default_work_place_id' => null, 'session_key' => null, 'is_active' => true]
            );
        }
    }
}

