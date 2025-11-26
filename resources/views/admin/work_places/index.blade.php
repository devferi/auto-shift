@extends('layouts.connect')

@section('menu.work_places','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Tempat / Unit Kerja</h5>
    <a href="{{ route('admin.work_places.create') }}" class="btn btn-primary">Tambah</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($workPlaces as $wp)
                    <tr>
                        <td>{{ $wp->code }}</td>
                        <td>{{ $wp->name }}</td>
                        <td>{{ $wp->description }}</td>
                        <td>{{ $wp->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td>
                            <a href="{{ route('admin.work_places.edit', $wp) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('admin.work_places.destroy', $wp) }}" method="post" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus tempat ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $workPlaces->links() }}
    </div>
    </div>
@endsection
