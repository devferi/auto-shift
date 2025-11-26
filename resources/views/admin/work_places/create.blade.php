@extends('layouts.connect')

@section('menu.work_places','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Tambah Tempat</h5>
    <a href="{{ route('admin.work_places.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('admin.work_places.store') }}">
            @csrf
            @include('admin.work_places.form')
            <button class="btn btn-primary" type="submit">Simpan</button>
        </form>
    </div>
</div>
@endsection
