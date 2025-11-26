@extends('layouts.connect')

@section('menu.dashboard','active')

@section('content')
<div class="page-info d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="#">Apps</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>
    <div class="page-options">
        <a href="#" class="btn btn-secondary">Settings</a>
        <a href="#" class="btn btn-primary">Upgrade</a>
    </div>
    </div>

<div class="main-wrapper">
    <div class="row stats-row">
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Jobs Pending</h5>
                    <p class="mb-0">{{ \App\Models\AttendanceJob::where('status','pending')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Jobs Done (Hari Ini)</h5>
                    <p class="mb-0">{{ \App\Models\AttendanceJob::whereDate('date', now())->where('status','done')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Karyawan Aktif</h5>
                    <p class="mb-0">{{ \App\Models\Employee::where('is_active',true)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Job Terbaru</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Tipe</th>
                                <th>Message</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach(\App\Models\AttendanceJob::orderBy('run_at','desc')->limit(10)->get() as $job)
                                <tr>
                                    <td>{{ $job->run_at }}</td>
                                    <td>{{ $job->type }}</td>
                                    <td>{{ $job->message }}</td>
                                    <td>{{ $job->status }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
