<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $waServer = SystemSetting::get('wa_server_url', 'https://wa.posyandudigital.my.id/');
        $waSession = SystemSetting::get('wa_session', 'waiskak');
        $waTarget = SystemSetting::get('wa_target_number', '6281333994823');
        $waTimeout = (int) SystemSetting::get('wa_timeout_seconds', 15);
        $waConnectTimeout = (int) SystemSetting::get('wa_connect_timeout_seconds', 5);
        return view('admin.settings.edit', compact('waServer', 'waSession', 'waTarget', 'waTimeout', 'waConnectTimeout'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'wa_server_url' => ['required', 'url'],
            'wa_session' => ['required', 'string', 'max:255'],
            'wa_target_number' => ['required', 'regex:/^\d+$/'],
            'wa_timeout_seconds' => ['nullable', 'integer', 'min:5', 'max:120'],
            'wa_connect_timeout_seconds' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        SystemSetting::set('wa_server_url', $data['wa_server_url']);
        SystemSetting::set('wa_session', $data['wa_session']);
        SystemSetting::set('wa_target_number', $data['wa_target_number']);
        if (isset($data['wa_timeout_seconds'])) { SystemSetting::set('wa_timeout_seconds', (string) $data['wa_timeout_seconds']); }
        if (isset($data['wa_connect_timeout_seconds'])) { SystemSetting::set('wa_connect_timeout_seconds', (string) $data['wa_connect_timeout_seconds']); }

        return redirect()->route('admin.settings.edit')->with('status', 'Settings updated');
    }

    public function test(Request $request, \App\Services\WhatsAppGateway $wa)
    {
        $to = SystemSetting::get('wa_target_number');
        $session = SystemSetting::get('wa_session');
        if (!$to || !$session) {
            return redirect()->route('admin.settings.edit')->with('status', 'Isi target number dan session terlebih dahulu');
        }
        try {
             $resp = $wa->sendText($to, 'test#connect', $session);
            return redirect()->route('admin.settings.edit')->with('status', 'WA OK: '.$resp['status']);
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings.edit')->with('status', 'WA ERROR: '.$e->getMessage());
        }
    }
}
