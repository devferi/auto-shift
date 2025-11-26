@extends('layouts.connect')

@section('menu.shifts','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Edit Shift Time Rule</h5>
    <a href="{{ route('admin.shift_time_rules.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('admin.shift_time_rules.update', $rule) }}">
            @csrf
            @method('PUT')
            @include('admin.shift_time_rules.form')
            <button class="btn btn-primary" type="submit">Update</button>
        </form>
    </div>
</div>
@endsection
