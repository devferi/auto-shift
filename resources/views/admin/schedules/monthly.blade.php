@extends('layouts.connect')

@section('menu.schedules_monthly','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Jadwal Bulanan</h5>
    <form method="get" class="form-inline">
        <label class="mr-2">Bulan</label>
        <input type="month" name="month" value="{{ request('month', $start->format('Y-m')) }}" class="form-control mr-2">
        <button class="btn btn-secondary">Tampilkan</button>
    </form>
 </div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive" style="overflow-x:auto; -webkit-overflow-scrolling: touch;">
            <table class="table table-bordered table-sm schedule-grid">
                <thead>
                    <tr>
                        <th class="col-name">Karyawan</th>
                        @for($d=1; $d<=$days; $d++)
                            @php($iso = $start->copy()->setDay($d)->isoWeekday())
                            <th class="{{ in_array($iso,[6,7]) ? 'weekend' : '' }}">{{ $d }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                @foreach($employees as $e)
                    <tr>
                        <td class="col-name">{{ $e->name }}</td>
                        @for($d=1; $d<=$days; $d++)
                            @php($sc = $grid[$e->id][$d] ?? null)
                            @php($code = $sc ? optional($sc->shift)->code : '')
                            @php($iso = $start->copy()->setDay($d)->isoWeekday())
                            <td class="text-center {{ $code ? 'code-'.$code : '' }} {{ in_array($iso,[6,7]) ? 'weekend' : '' }}">{{ $code }}</td>
                        @endfor
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
 </div>
@endsection

@push('styles')
<style>
    .schedule-grid { font-size: 10px; line-height: 1.2; }
    .schedule-grid th, .schedule-grid td { padding: .15rem .25rem; }
    .schedule-grid thead th { position: sticky; top: 0; background: #fff; z-index: 2; }
    .schedule-grid .col-name { position: sticky; left: 0; background: #fff; z-index: 3; min-width: 110px; max-width: 220px; }
    .schedule-grid th:not(.col-name), .schedule-grid td:not(.col-name) { min-width: 26px; white-space: nowrap; }
    /* Weekend columns */
    .schedule-grid th.weekend { background: #fdecea; color: #b91c1c; }
    .schedule-grid td.weekend { background: #fff5f5; }
    /* Shift code colors */
    .schedule-grid td.code-PP1IPTI { background: #e0f2fe; color: #1e3a8a; }
    .schedule-grid td.code-PP2IPTI { background: #dbeafe; color: #1d4ed8; }
    .schedule-grid td.code-SP1IPTI { background: #ecfdf5; color: #065f46; }
    .schedule-grid td.code-SP2IPTI { background: #dcfce7; color: #166534; }
    /* Empty cells subtle */
    .schedule-grid td:not([class*="code-"]) { background: #f9fafb; color: #6b7280; }
    @media (max-width: 1200px) {
        .schedule-grid { font-size: 9px; }
        .schedule-grid th, .schedule-grid td { padding: .12rem .2rem; }
        .schedule-grid .col-name { min-width: 100px; }
    }
    @media (max-width: 768px) {
        .schedule-grid { font-size: 8.5px; }
        .schedule-grid th, .schedule-grid td { padding: .1rem .18rem; }
        .schedule-grid .col-name { min-width: 90px; }
    }
</style>
@endpush
