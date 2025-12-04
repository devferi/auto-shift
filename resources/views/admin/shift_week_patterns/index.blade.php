@extends('layouts.connect')
@section('menu.shift_week_patterns','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Pola Shift Mingguan</h5>
    <div class="text-muted">Tanggal: {{ $today->format('Y-m-d') }}</div>
    </div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Kode</th>
                        <th>Tempat</th>
                        <th>Mulai</th>
                        <th>Siklus (minggu)</th>
                        <th>Jenis</th>
                        <th>Items</th>
                        <th>Shift Posisi</th>
                        <th>Shift Hari Ini</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($patterns as $p)
                    <tr>
                        <td>{{ optional($p->employee)->name }}</td>
                        <td>{{ optional($p->employee)->person_code }}</td>
                        <td>{{ optional($p->workPlace)->name }}</td>
                        <td>{{ $p->start_date?->format('Y-m-d') }}</td>
                        <td>{{ $p->cycle_length_weeks }}</td>
                        <td>{{ $p->kind }}</td>
                        <td>
                            @foreach($p->items->sortBy('order_index') as $it)
                                <div>#{{ $it->order_index }} {{ optional($it->shift)->code }} ({{ $it->duration_weeks }}w)</div>
                            @endforeach
                        </td>
                        <td>{{ optional($p->current_shift)->code }}</td>
                        <td>{{ optional($p->today_shift)->code }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $patterns->links() }}
    </div>
 </div>
@endsection
