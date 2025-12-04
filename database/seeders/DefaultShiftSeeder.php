<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;
use App\Models\ShiftTimeRule;

class DefaultShiftSeeder extends Seeder
{
    public function run(): void
    {
        $pp1 = Shift::firstOrCreate(
            ['code' => 'PP1IPTI'],
            ['name' => 'Pagi Senin - Kamis', 'random_before_minutes' => 15, 'random_after_minutes' => 15, 'is_active' => true]
        );
        $pp2 = Shift::firstOrCreate(
            ['code' => 'PP2IPTI'],
            ['name' => 'Pagi Jumat', 'random_before_minutes' => 15, 'random_after_minutes' => 15, 'is_active' => true]
        );
        $sp1 = Shift::firstOrCreate(
            ['code' => 'SP1IPTI'],
            ['name' => 'Siang Senin - Kamis', 'random_before_minutes' => 15, 'random_after_minutes' => 15, 'is_active' => true]
        );
        $sp2 = Shift::firstOrCreate(
            ['code' => 'SP2IPTI'],
            ['name' => 'Siang Jumat', 'random_before_minutes' => 15, 'random_after_minutes' => 15, 'is_active' => true]
        );

        foreach ([1,2,3,4] as $d) {
            ShiftTimeRule::updateOrCreate(
                ['shift_id' => $pp1->id, 'day_of_week' => $d],
                ['start_time' => '08:00:00', 'end_time' => '16:00:00', 'is_active' => true]
            );
            ShiftTimeRule::updateOrCreate(
                ['shift_id' => $sp1->id, 'day_of_week' => $d],
                ['start_time' => '13:00:00', 'end_time' => '21:00:00', 'is_active' => true]
            );
        }

        ShiftTimeRule::updateOrCreate(
            ['shift_id' => $pp2->id, 'day_of_week' => 5],
            ['start_time' => '08:00:00', 'end_time' => '15:00:00', 'is_active' => true]
        );
        ShiftTimeRule::updateOrCreate(
            ['shift_id' => $sp2->id, 'day_of_week' => 5],
            ['start_time' => '14:00:00', 'end_time' => '19:30:00', 'is_active' => true]
        );
    }
}
