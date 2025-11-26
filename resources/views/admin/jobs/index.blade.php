@extends('layouts.connect')

@section('menu.jobs','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Attendance Jobs</h5>
    <div>
        <form action="{{ route('admin.attendance_jobs.generate') }}" method="post" class="d-inline-flex align-items-center mr-2">
            @csrf
            <input type="date" name="date" value="{{ request('date') ?? now()->toDateString() }}" class="form-control mr-2" style="width: 180px;">
            <button class="btn btn-success">Generate</button>
        </form>
        <form action="{{ route('admin.attendance_jobs.run') }}" method="post" class="d-inline">
            @csrf
            <button class="btn btn-warning">Run Pending</button>
        </form>
        <a href="{{ route('admin.attendance_jobs.create') }}" class="btn btn-primary">Tambah</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="form-row">
            <div class="form-group col-md-2">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">Semua</option>
                    @foreach(['pending','done','failed'] as $st)
                        <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Tipe</label>
                <select name="type" class="form-control">
                    <option value="">Semua</option>
                    @foreach(['login','logout'] as $tp)
                        <option value="{{ $tp }}" {{ request('type')===$tp?'selected':'' }}>{{ ucfirst($tp) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Tanggal Shift</label>
                <input type="date" name="date" value="{{ request('date') }}" class="form-control">
            </div>
            <div class="form-group col-md-3">
                <label>Run At Mulai</label>
                <input type="datetime-local" name="start" value="{{ request('start') }}" class="form-control">
            </div>
            <div class="form-group col-md-3">
                <label>Run At Akhir</label>
                <input type="datetime-local" name="end" value="{{ request('end') }}" class="form-control">
            </div>
            <div class="form-group col-md-3">
                <label>Karyawan</label>
                <select name="employee_id" class="form-control">
                    <option value="">Semua</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}" {{ (string) request('employee_id') === (string) $e->id ? 'selected' : '' }}>{{ $e->name }} ({{ $e->person_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Tempat</label>
                <select name="work_place_id" class="form-control">
                    <option value="">Semua</option>
                    @foreach($workPlaces as $wp)
                        <option value="{{ $wp->id }}" {{ (string) request('work_place_id') === (string) $wp->id ? 'selected' : '' }}>{{ $wp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Shift</label>
                <select name="shift_id" class="form-control">
                    <option value="">Semua</option>
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}" {{ (string) request('shift_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-12 mt-2">
                <button class="btn btn-secondary">Filter</button>
                <a href="{{ route('admin.attendance_jobs.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Run At</th>
                    <th>Shift Date</th>
                    <th>Tipe</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Karyawan</th>
                    <th>Tempat</th>
                    <th>Shift</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($jobs as $job)
                    <tr>
                        <td>{{ optional($job->run_at)->format('Y-m-d H:i:s') }}</td>
                        <td>{{ optional($job->date)->format('Y-m-d') }}</td>
                        <td>{{ $job->type }}</td>
                        <td>{{ $job->message }}</td>
                        <td>{{ $job->status }}</td>
                        <td>{{ $job->attempts }}</td>
                        <td>{{ $job->employee->name }}</td>
                        <td>{{ $job->workPlace->name }}</td>
                        <td>{{ $job->shift->code }}</td>
                        <td>
                            <a href="{{ route('admin.attendance_jobs.edit', $job) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('admin.attendance_jobs.destroy', $job) }}" method="post" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus job ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $jobs->links() }}
    </div>
</div>
@endsection
