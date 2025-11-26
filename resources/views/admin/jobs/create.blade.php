@extends('layouts.connect')

@section('menu.jobs','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Tambah Job</h5>
    <a href="{{ route('admin.attendance_jobs.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('admin.attendance_jobs.store') }}">
            @csrf
            @include('admin.jobs.form')
            <button class="btn btn-primary" type="submit">Simpan</button>
        </form>
    </div>
</div>
@endsection
