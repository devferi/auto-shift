@extends('layouts.connect')

@section('menu.logs_whatsapp','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Log WhatsApp</h5>
    <form method="get" class="form-inline">
        <input type="date" name="date" value="{{ request('date') }}" class="form-control mr-2">
        <select name="status" class="form-control mr-2">
            <option value="">Semua Status</option>
            @foreach(['pending','done','failed'] as $st)
                <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary">Filter</button>
        <a href="{{ route('admin.logs.whatsapp') }}" class="btn btn-light ml-2">Reset</a>
    </form>
    </div>

<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">API Requests</h6>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Method</th>
                    <th>URL</th>
                    <th>Body</th>
                    <th>Status</th>
                    <th>Response</th>
                </tr>
                </thead>
                <tbody>
                @foreach($apiLogs as $log)
                    <tr>
                        <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $log->method }}</td>
                        <td style="max-width:420px; word-break:break-all;">{{ $log->url }}</td>
                        <td><pre class="mb-0" style="white-space:pre-wrap; max-width:420px;">{{ $log->request_body }}</pre></td>
                        <td>{{ $log->response_status }}</td>
                        <td><pre class="mb-0" style="white-space:pre-wrap; max-width:420px;">{{ $log->response_body }}</pre></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $apiLogs->links() }}
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6 class="card-title">Jobs Execution</h6>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Run At</th>
                    <th>Status</th>
                    <th>Request URL</th>
                    <th>Request Body</th>
                    <th>Response</th>
                </tr>
                </thead>
                <tbody>
                @foreach($jobs as $job)
                    <tr>
                        <td>{{ optional($job->run_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $job->status }}</td>
                        <td style="max-width:420px; word-break:break-all;">{{ $job->api_url }}</td>
                        <td><pre class="mb-0" style="white-space:pre-wrap; max-width:420px;">{{ $job->api_request_body }}</pre></td>
                        <td><pre class="mb-0" style="white-space:pre-wrap; max-width:420px;">{{ $job->api_response }}</pre></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $jobs->links() }}
    </div>
 </div>
@endsection
