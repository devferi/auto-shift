@extends('layouts.connect')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Karyawan</h5>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">Tambah</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>WA</th>
                        <th>Tempat Default</th>
                        <th>Session</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($employees as $e)
                    <tr>
                        <td>{{ $e->person_code }}</td>
                        <td>{{ $e->name }}</td>
                        <td>{{ $e->wa_number }}</td>
                        <td>{{ optional($e->defaultWorkPlace)->name }}</td>
                        <td>{{ $e->session_key }}</td>
                        <td>{{ $e->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td>
                            <a href="{{ route('admin.employees.edit', $e) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('admin.employees.destroy', $e) }}" method="post" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus karyawan ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $employees->links() }}
    </div>
</div>
@endsection
