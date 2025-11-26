<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\WhatsAppGateway;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request, WhatsAppGateway $wa)
    {
        $resp = $wa->getSessions();
        $sessions = null;
        $body = $resp['body'] ?? '';
        try { $sessions = json_decode($body, true); } catch (\Throwable $e) {}
        $default = SystemSetting::get('wa_session');
        $startUrl = $default ? $wa->startSessionUrl($default) : null;
        $baseUrl = rtrim(SystemSetting::get('wa_server_url', config('whatsapp.base_url')), '/');
        return view('admin.sessions.index', compact('sessions','resp','default','startUrl','baseUrl'));
    }

    public function start(Request $request, WhatsAppGateway $wa)
    {
        $data = $request->validate(['session' => ['required','string']]);
        $url = $wa->startSessionUrl($data['session']);
        return redirect()->away($url);
    }

    public function logout(Request $request, WhatsAppGateway $wa)
    {
        $data = $request->validate(['session' => ['required','string']]);
        $resp = $wa->logoutSession($data['session']);
        return redirect()->route('admin.sessions.index')->with('status', 'Logout '.$data['session'].' status '.$resp['status']);
    }
}
