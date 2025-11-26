@extends('layouts.connect')

@section('menu.shifts','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Master Shift</h5>
    <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary">Tambah</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Rand. Before</th>
                    <th>Rand. After</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($shifts as $s)
                    <tr>
                        <td>{{ $s->code }}</td>
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->random_before_minutes }}m</td>
                        <td>{{ $s->random_after_minutes }}m</td>
                        <td>{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td>
                            <a href="{{ route('admin.shifts.edit', $s) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('admin.shifts.destroy', $s) }}" method="post" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus shift ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $shifts->links() }}
    </div>
    </div>
@endsection
