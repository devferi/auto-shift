<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;
use App\Models\ShiftTimeRule;

class DefaultShiftSeeder extends Seeder
{
    public function run(): void
    {
        $pagi = Shift::firstOrCreate(
            ['code' => 'PAGI'],
            ['name' => 'Shift Pagi', 'random_before_minutes' => 15, 'random_after_minutes' => 15, 'is_active' => true]
        );
        $siang = Shift::firstOrCreate(
            ['code' => 'SIANG'],
            ['name' => 'Shift Siang', 'random_before_minutes' => 15, 'random_after_minutes' => 15, 'is_active' => true]
        );

        foreach ([1,2,3,4] as $d) {
            ShiftTimeRule::updateOrCreate(
                ['shift_id' => $pagi->id, 'day_of_week' => $d],
                ['start_time' => '08:00:00', 'end_time' => '16:00:00', 'is_active' => true]
            );
            ShiftTimeRule::updateOrCreate(
                ['shift_id' => $siang->id, 'day_of_week' => $d],
                ['start_time' => '13:00:00', 'end_time' => '21:00:00', 'is_active' => true]
            );
        }

        ShiftTimeRule::updateOrCreate(
            ['shift_id' => $pagi->id, 'day_of_week' => 5],
            ['start_time' => '08:00:00', 'end_time' => '13:00:00', 'is_active' => true]
        );
        ShiftTimeRule::updateOrCreate(
            ['shift_id' => $siang->id, 'day_of_week' => 5],
            ['start_time' => '14:00:00', 'end_time' => '19:30:00', 'is_active' => true]
        );
    }
}
