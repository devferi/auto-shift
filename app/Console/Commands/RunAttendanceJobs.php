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
        $jobs = AttendanceJob::with(['employee'])->where('status', 'pending')->where('run_at', '<=', $now)->orderBy('run_at')->limit(100)->get();
        foreach ($jobs as $job) {
            try {
                $to = SystemSetting::get('wa_target_number', $job->employee->wa_number);
                $session = SystemSetting::get('wa_session', $job->employee->session_key);
                $resp = $wa->sendText($to, $job->message, $session);
                $job->status = 'done';
                $job->api_url = $resp['url'];
                $job->api_response = $resp['body'];
                $job->api_request_body = json_encode($resp['request']);
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
