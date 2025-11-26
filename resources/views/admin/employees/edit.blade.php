@extends('layouts.connect')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Edit Karyawan</h5>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('admin.employees.update', $employee) }}">
            @csrf
            @method('PUT')
            @include('admin.employees.form')
            <button class="btn btn-primary" type="submit">Update</button>
        </form>
    </div>
</div>
@endsection
