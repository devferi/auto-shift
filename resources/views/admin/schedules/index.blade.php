@extends('layouts.connect')

@section('menu.schedules','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Jadwal Harian</h5>
    <div>
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">Tambah</a>
        <a href="{{ route('admin.schedules.import') }}" class="btn btn-secondary">Upload</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="form-row">
            <div class="form-group col-md-3">
                <label>Tanggal Mulai</label>
                <input type="date" name="start" value="{{ request('start') }}" class="form-control">
            </div>
            <div class="form-group col-md-3">
                <label>Tanggal Akhir</label>
                <input type="date" name="end" value="{{ request('end') }}" class="form-control">
            </div>
            <div class="form-group col-md-2">
                <label>Karyawan</label>
                <select name="employee_id" class="form-control">
                    <option value="">Semua</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}" {{ (string) request('employee_id') === (string) $e->id ? 'selected' : '' }}>{{ $e->name }} ({{ $e->person_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Tempat</label>
                <select name="work_place_id" class="form-control">
                    <option value="">Semua</option>
                    @foreach($workPlaces as $wp)
                        <option value="{{ $wp->id }}" {{ (string) request('work_place_id') === (string) $wp->id ? 'selected' : '' }}>{{ $wp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
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
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Karyawan</th>
                    <th>Tempat</th>
                    <th>Shift</th>
                    <th>Login Msg</th>
                    <th>Logout Msg</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($schedules as $sc)
                    <tr>
                        <td>{{ $sc->date->toDateString() }}</td>
                        <td>{{ $sc->employee->name }} ({{ $sc->employee->person_code }})</td>
                        <td>{{ $sc->workPlace->name }}</td>
                        <td>{{ $sc->shift->code }}</td>
                        <td>{{ $sc->login_message }}</td>
                        <td>{{ $sc->logout_message }}</td>
                        <td>
                            <a href="{{ route('admin.schedules.edit', $sc) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('admin.schedules.destroy', $sc) }}" method="post" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus jadwal ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $schedules->links() }}
    </div>
</div>
@endsection
