<?php
namespace App\Services;

use GuzzleHttp\Client;
use App\Models\SystemSetting;
use App\Models\WhatsappApiLog;

class WhatsAppGateway
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function sendText(string $to, string $text, ?string $session = null): array
    {
        $base = rtrim(SystemSetting::get('wa_server_url', config('whatsapp.base_url')), '/');
        $endpoint = $base.'/message/send-text';
        $sessionKey = $session ?: SystemSetting::get('wa_session', config('whatsapp.default_session'));
        $body = [
            'session' => $sessionKey,
            'to' => $to,
            'text' => $text,
        ];
        $timeout = (int) SystemSetting::get('wa_timeout_seconds', 15);
        $connectTimeout = (int) SystemSetting::get('wa_connect_timeout_seconds', 5);
        $response = $this->client->post($endpoint, [
            'json' => $body,
            'headers' => ['Accept' => 'application/json'],
            'http_errors' => false,
            'timeout' => $timeout,
            'connect_timeout' => $connectTimeout,
        ]);
        WhatsappApiLog::create([
            'method' => 'POST',
            'url' => $endpoint,
            'request_body' => json_encode($body),
            'response_status' => $response->getStatusCode(),
            'response_body' => (string) $response->getBody(),
        ]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'url' => $endpoint,
            'request' => $body,
        ];
    }

    public function getSessions(): array
    {
        $base = rtrim(SystemSetting::get('wa_server_url', config('whatsapp.base_url')), '/');
        $endpoint = $base.'/session';
        $response = $this->client->get($endpoint, [
            'headers' => ['Accept' => 'application/json'],
            'http_errors' => false,
            'timeout' => (int) SystemSetting::get('wa_timeout_seconds', 15),
            'connect_timeout' => (int) SystemSetting::get('wa_connect_timeout_seconds', 5),
        ]);
        WhatsappApiLog::create([
            'method' => 'GET',
            'url' => $endpoint,
            'request_body' => null,
            'response_status' => $response->getStatusCode(),
            'response_body' => (string) $response->getBody(),
        ]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'url' => $endpoint,
        ];
    }

    public function logoutSession(string $session): array
    {
        $base = rtrim(SystemSetting::get('wa_server_url', config('whatsapp.base_url')), '/');
        $endpoint = $base.'/session/logout';
        $response = $this->client->get($endpoint, [
            'query' => ['session' => $session],
            'headers' => ['Accept' => 'application/json'],
            'http_errors' => false,
            'timeout' => (int) SystemSetting::get('wa_timeout_seconds', 15),
            'connect_timeout' => (int) SystemSetting::get('wa_connect_timeout_seconds', 5),
        ]);
        WhatsappApiLog::create([
            'method' => 'GET',
            'url' => $endpoint.'?session='.$session,
            'request_body' => null,
            'response_status' => $response->getStatusCode(),
            'response_body' => (string) $response->getBody(),
        ]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'url' => $endpoint,
        ];
    }

    public function startSessionUrl(string $session): string
    {
        $base = rtrim(SystemSetting::get('wa_server_url', config('whatsapp.base_url')), '/');
        return $base.'/session/start?session='.urlencode($session);
    }
}
