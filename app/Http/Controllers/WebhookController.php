<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WhatsappApiLog;

class WebhookController extends Controller
{
    public function session(Request $request)
    {
        $payload = $request->getContent();
        WhatsappApiLog::create([
            'method' => 'WEBHOOK',
            'url' => '/webhook/session',
            'request_body' => $payload,
            'response_status' => 200,
            'response_body' => 'ok',
        ]);
        return response()->json(['status' => 'ok']);
    }

    public function message(Request $request)
    {
        $payload = $request->getContent();
        WhatsappApiLog::create([
            'method' => 'WEBHOOK',
            'url' => '/webhook/message',
            'request_body' => $payload,
            'response_status' => 200,
            'response_body' => 'ok',
        ]);
        return response()->json(['status' => 'ok']);
    }
}

