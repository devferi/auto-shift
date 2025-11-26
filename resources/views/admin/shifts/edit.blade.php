@extends('layouts.connect')

@section('menu.shifts','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Edit Shift</h5>
    <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('admin.shifts.update', $shift) }}">
            @csrf
            @method('PUT')
            @include('admin.shifts.form')
            <button class="btn btn-primary" type="submit">Update</button>
        </form>
    </div>
</div>
@endsection
