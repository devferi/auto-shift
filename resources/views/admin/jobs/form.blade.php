<div class="form-row">
    <div class="form-group col-md-4">
        <label>Karyawan</label>
        <select name="employee_id" class="form-control" required>
            @foreach($employees as $e)
                <option value="{{ $e->id }}" {{ (string) old('employee_id', $job->employee_id ?? '') === (string) $e->id ? 'selected' : '' }}>{{ $e->name }} ({{ $e->person_code }})</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Tempat</label>
        <select name="work_place_id" class="form-control" required>
            @foreach($workPlaces as $wp)
                <option value="{{ $wp->id }}" {{ (string) old('work_place_id', $job->work_place_id ?? '') === (string) $wp->id ? 'selected' : '' }}>{{ $wp->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Shift</label>
        <select name="shift_id" class="form-control" required>
            @foreach($shifts as $s)
                <option value="{{ $s->id }}" {{ (string) old('shift_id', $job->shift_id ?? '') === (string) $s->id ? 'selected' : '' }}>{{ $s->code }} - {{ $s->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-3">
        <label>Tanggal Shift</label>
        <input type="date" name="date" class="form-control" value="{{ old('date', optional($job->date ?? null)->toDateString()) }}" required>
    </div>
    <div class="form-group col-md-3">
        <label>Tipe</label>
        <select name="type" class="form-control" required>
            @foreach(['login','logout'] as $tp)
                <option value="{{ $tp }}" {{ old('type', $job->type ?? '')===$tp?'selected':'' }}>{{ ucfirst($tp) }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label>Message</label>
        <input type="text" name="message" class="form-control" value="{{ old('message', $job->message ?? '') }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-4">
        <label>Run At</label>
        <input type="datetime-local" name="run_at" class="form-control" value="{{ old('run_at', optional($job->run_at ?? null)->format('Y-m-d\TH:i')) }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Status</label>
        <select name="status" class="form-control">
            @foreach(['pending','done','failed'] as $st)
                <option value="{{ $st }}" {{ old('status', $job->status ?? 'pending')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Attempts</label>
        <input type="number" min="0" name="attempts" class="form-control" value="{{ old('attempts', $job->attempts ?? 0) }}">
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>API URL</label>
        <input type="text" name="api_url" class="form-control" value="{{ old('api_url', $job->api_url ?? '') }}">
    </div>
    <div class="form-group col-md-6">
        <label>API Response</label>
        <textarea name="api_response" rows="3" class="form-control">{{ old('api_response', $job->api_response ?? '') }}</textarea>
    </div>
</div>
