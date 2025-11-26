@extends('layouts.connect')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Tambah Karyawan</h5>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('admin.employees.store') }}">
            @csrf
            @include('admin.employees.form')
            <button class="btn btn-primary" type="submit">Simpan</button>
        </form>
    </div>
</div>
@endsection
