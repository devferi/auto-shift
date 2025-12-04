<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Employee;
use App\Models\EmployeeShiftSchedule;
use App\Models\Shift;
use App\Models\WorkPlace;
use App\Models\ShiftWeekPattern;
use App\Models\ShiftWeekPatternItem;
use Illuminate\Support\Facades\Artisan;

class DecemberScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $file = base_path('Jadwal Inpresso Programmer Nov - Des.xlsx');
        $wp = WorkPlace::firstOrCreate(['code' => 'OFFICE'], ['name' => 'Kantor', 'description' => null, 'is_active' => true]);
        $pp1Id = Shift::where('code','PP1IPTI')->value('id');
        $pp2Id = Shift::where('code','PP2IPTI')->value('id');
        $sp1Id = Shift::where('code','SP1IPTI')->value('id');
        $sp2Id = Shift::where('code','SP2IPTI')->value('id');
        $data = $this->readXlsxGrid($file);
        if (!$data) { return; }
        $header = $data[1] ?? [];
        $days = [];
        foreach ($header as $col => $val) {
            $d = (int) preg_replace('/[^0-9]/','', (string) $val);
            if ($d >= 1 && $d <= 31) { $days[$col] = $d; }
        }
        $year = (int) now()->year;
        $month = 11;
        foreach ($data as $row => $cols) {
            if ($row === 1) { continue; }
            $name = trim((string) ($cols['A'] ?? $cols[1] ?? ''));
            if ($name === '') { continue; }
            $emp = Employee::where('name',$name)->first();
            if (!$emp) { continue; }
            foreach ($days as $col => $day) {
                $txt = trim((string) ($cols[$col] ?? ''));
                if ($txt === '') { continue; }
                $date = Carbon::create($year, $month, $day)->startOfDay();
                $dow = $date->isoWeekday();
                if (in_array($dow, [6,7], true)) { continue; }
                $sid = $this->mapShift($txt, $dow, $pp1Id, $pp2Id, $sp1Id, $sp2Id);
                if (!$sid) { continue; }
                EmployeeShiftSchedule::updateOrCreate(
                    ['employee_id' => $emp->id, 'date' => $date->toDateString()],
                    ['work_place_id' => $wp->id, 'shift_id' => $sid]
                );
            }
        }

        // Generate schedules for the whole November after seeding
        Artisan::call('attendance:generate-schedules', ['--month' => sprintf('%04d-%02d', $year, $month)]);


    }

    protected function mapShift(string $txt, int $dow, ?int $pp1Id, ?int $pp2Id, ?int $sp1Id, ?int $sp2Id): ?int
    {
        $t = strtoupper(trim($txt));
        if ($t === 'OFF' || str_contains($t, 'OFF')) { return null; }
        if (str_contains($t, 'PP2') || $t === 'PP2IPTI') { return $pp2Id; }
        if (str_contains($t, 'PP1') || $t === 'PP1IPTI') { return $pp1Id; }
        if (str_contains($t, 'SP2') || $t === 'SP2IPTI') { return $sp2Id; }
        if (str_contains($t, 'SP1') || $t === 'SP1IPTI') { return $sp1Id; }
        if (str_contains($t, 'PAGI') || $t === 'P' || $t === 'PG') { return $dow === 5 ? $pp2Id : $pp1Id; }
        if (str_contains($t, 'SIANG') || $t === 'S') { return $dow === 5 ? $sp2Id : $sp1Id; }
        return null;
    }

    protected function readXlsxGrid(string $path): ?array
    {
        if (!is_file($path)) { return null; }
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) { return null; }
        $shared = [];
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml) {
            $sx = @simplexml_load_string($xml);
            if ($sx) {
                foreach ($sx->si as $si) {
                    $text = '';
                    foreach ($si->t as $t) { $text .= (string) $t; }
                    if ($text === '' && isset($si->r)) {
                        foreach ($si->r as $r) { $text .= (string) ($r->t ?? ''); }
                    }
                    $shared[] = $text;
                }
            }
        }
        // Try to find worksheet for November by sheet name containing "Nov" or "November"
        $targetWorksheet = 'xl/worksheets/sheet1.xml';
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml && $relsXml) {
            $wb = @simplexml_load_string($workbookXml);
            $rels = @simplexml_load_string($relsXml);
            if ($wb && $rels) {
                $wb->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                $candidates = [];
                foreach ($wb->sheets->sheet as $sheetEl) {
                    $name = (string) $sheetEl['name'];
                    $rid = (string) $sheetEl->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                    if ($rid === '') { continue; }
                    foreach ($rels->Relationship as $rel) {
                        if ((string)$rel['Id'] === $rid) {
                            $target = 'xl/'.ltrim((string)$rel['Target'],'/');
                            $candidates[] = [$name, $target];
                        }
                    }
                }
                $chosen = null;
                foreach ($candidates as [$name,$target]) {
                    $up = strtoupper($name);
                    if (str_contains($up, 'NOV')) { $chosen = $target; break; }
                }
                if ($chosen) { $targetWorksheet = $chosen; }
            }
        }
        $sheet = $zip->getFromName($targetWorksheet);
        $zip->close();
        if (!$sheet) { return null; }
        $sx = @simplexml_load_string($sheet);
        if (!$sx) { return null; }
        $grid = [];
        foreach ($sx->sheetData->row as $row) {
            $rIdx = (int) $row['r'];
            if ($rIdx <= 0) { continue; }
            $grid[$rIdx] = [];
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $col = preg_replace('/[0-9]/','', $ref);
                $val = (string) ($c->v ?? '');
                $type = (string) ($c['t'] ?? '');
                if ($type === 's') {
                    $ix = (int) $val;
                    $grid[$rIdx][$col] = $shared[$ix] ?? '';
                } else {
                    $grid[$rIdx][$col] = $val;
                }
            }
        }
        return $grid;
    }
}
