@extends('layouts.connect')

@section('menu.settings','active')

@section('content')
<div class="page-info d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="#">Apps</a></li>
            <li class="breadcrumb-item active" aria-current="page">Setting</li>
        </ol>
    </nav>
</div>

<div class="main-wrapper">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">WhatsApp</h5>
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    <form method="post" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label>Server WA</label>
                            <input type="url" class="form-control" name="wa_server_url" value="{{ old('wa_server_url', $waServer) }}" placeholder="https://example.com/">
                            @error('wa_server_url')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Session</label>
                            <input type="text" class="form-control" name="wa_session" value="{{ old('wa_session', $waSession) }}" placeholder="wa-session">
                            @error('wa_session')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Nomer Tujuan Absen</label>
                            <input type="text" class="form-control" name="wa_target_number" value="{{ old('wa_target_number', $waTarget) }}" placeholder="62xxxxxxxxxxx">
                            @error('wa_target_number')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Timeout (detik)</label>
                            <input type="number" class="form-control" name="wa_timeout_seconds" value="{{ old('wa_timeout_seconds', $waTimeout) }}" min="5" max="120">
                            @error('wa_timeout_seconds')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Connect Timeout (detik)</label>
                            <input type="number" class="form-control" name="wa_connect_timeout_seconds" value="{{ old('wa_connect_timeout_seconds', $waConnectTimeout) }}" min="1" max="60">
                            @error('wa_connect_timeout_seconds')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                    <form method="post" action="{{ route('admin.settings.test') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Tes Koneksi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
