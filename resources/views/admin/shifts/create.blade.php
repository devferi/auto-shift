@extends('layouts.connect')

@section('menu.shifts','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Tambah Shift</h5>
    <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('admin.shifts.store') }}">
            @csrf
            @include('admin.shifts.form')
            <button class="btn btn-primary" type="submit">Simpan</button>
        </form>
    </div>
</div>
@endsection
