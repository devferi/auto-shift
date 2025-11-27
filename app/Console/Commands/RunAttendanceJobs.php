<?php
namespace App\Console\Commands;

use App\Models\AttendanceJob;
use App\Models\SystemSetting;
use App\Services\WhatsAppGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RunAttendanceJobs extends Command
{
    protected $signature = 'attendance:run-jobs';
    protected $description = 'Execute pending attendance jobs by sending WhatsApp messages';

    public function handle(WhatsAppGateway $wa): int
    {
        $now = Carbon::now();
        $jobs = AttendanceJob::with(['employee','workPlace','shift'])
            ->where('status', 'pending')
            ->where('run_at', '<=', $now)
            ->orderBy('run_at')
            ->limit(100)
            ->get();
        foreach ($jobs as $job) {
            try {
                $to = SystemSetting::get('wa_target_number', $job->employee->wa_number);
                $session = SystemSetting::get('wa_session', $job->employee->session_key);
                $resp1 = $wa->sendText($to, 'Halo', $session);
                sleep(10);
                $resp2 = $wa->sendText($to, $job->message, $session);
                $ok = ($resp2['status'] ?? 0) >= 200 && ($resp2['status'] ?? 0) < 300;
                if ($ok) {
                    sleep(10);
                    $notifyTo = $job->employee->wa_number ?: SystemSetting::get('wa_notify_number', SystemSetting::get('wa_target_number'));
                    if ($notifyTo) {
                        $typeLabel = $job->type === 'logout' ? 'logout' : 'login';
                        $place = optional($job->workPlace)->name ?: optional($job->workPlace)->code;
                        $time = optional($job->run_at)->format('H:i');
                        $name = $job->employee->name;
                        $note = 'Absensi '.$typeLabel.' berhasil untuk '.$name.' di '.$place.' pada '.$time.'.';
                        $empSession = $job->employee->session_key ?: $session;
                        $wa->sendText($notifyTo, $note, $empSession);
                    }
                }
                $job->status = $ok ? 'done' : $job->status;
                $job->api_url = $resp2['url'];
                $job->api_response = $resp2['body'];
                $job->api_request_body = json_encode($resp2['request']);
                $job->attempts = $job->attempts + 1;
                $job->save();
            } catch (\Throwable $e) {
                try {
                    $base = rtrim(SystemSetting::get('wa_server_url'), '/');
                    $endpoint = $base.'/message/send-text';
                    \App\Models\WhatsappApiLog::create([
                        'method' => 'POST',
                        'url' => $endpoint,
                        'request_body' => json_encode(['session' => $session, 'to' => $to, 'text' => $job->message]),
                        'response_status' => null,
                        'response_body' => $e->getMessage(),
                    ]);
                } catch (\Throwable $ignore) {}
                $job->attempts = $job->attempts + 1;
                if ($job->attempts >= 3) {
                    $job->status = 'failed';
                }
                $job->api_response = $e->getMessage();
                $job->save();
            }
        }
        return self::SUCCESS;
    }
}
