@extends('layouts.connect')

@section('menu.shifts','active')

@php
    $days = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Shift Time Rules</h5>
    <div>
        <a href="{{ route('admin.shift_time_rules.create') }}" class="btn btn-primary">Tambah</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="form-inline">
            <label class="mr-2">Filter Shift</label>
            <select name="shift_id" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">Semua</option>
                @foreach($shifts as $s)
                    <option value="{{ $s->id }}" {{ (string)$shiftId === (string)$s->id ? 'selected' : '' }}>{{ $s->code }} - {{ $s->name }}</option>
                @endforeach
            </select>
            @if($shiftId)
                <a href="{{ route('admin.shift_time_rules.index') }}" class="btn btn-sm btn-secondary">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Shift</th>
                    <th>Hari</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rules as $r)
                    <tr>
                        <td>{{ $r->shift->code }} - {{ $r->shift->name }}</td>
                        <td>{{ $days[$r->day_of_week] ?? $r->day_of_week }}</td>
                        <td>{{ substr($r->start_time,0,5) }}</td>
                        <td>{{ substr($r->end_time,0,5) }}</td>
                        <td>{{ $r->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td>
                            <a href="{{ route('admin.shift_time_rules.edit', $r) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('admin.shift_time_rules.destroy', $r) }}" method="post" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus rule ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $rules->links() }}
    </div>
    </div>
@endsection
