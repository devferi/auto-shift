@extends('layouts.connect')

@section('menu.schedules_import','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Upload Jadwal Mingguan (CSV)</h5>
    <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('admin.schedules.import.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>File CSV</label>
                <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                <small class="form-text text-muted">Kolom wajib: date, person_code, work_place_code, shift_code. Opsional: login_message, logout_message.</small>
            </div>
            <button class="btn btn-primary" type="submit">Upload</button>
        </form>

        <hr>
        <p class="mb-1">Contoh CSV:</p>
        <pre class="p-2 bg-light border">date,person_code,work_place_code,shift_code,login_message,logout_message
2025-12-01,af1,cs1,PAGI,,
2025-12-02,af1,cs1,PAGI,,
2025-12-03,af1,cs1,PAGI,,
2025-12-04,af1,cs1,PAGI,,
2025-12-05,af1,cs1,PAGI,,
        </pre>
    </div>
</div>
@endsection
