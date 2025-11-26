<div class="form-row">
    <div class="form-group col-md-4">
        <label>Karyawan</label>
        <select name="employee_id" class="form-control" required>
            @foreach($employees as $e)
                <option value="{{ $e->id }}" {{ (string) old('employee_id', $schedule->employee_id ?? '') === (string) $e->id ? 'selected' : '' }}>{{ $e->name }} ({{ $e->person_code }})</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Tempat</label>
        <select name="work_place_id" class="form-control" required>
            @foreach($workPlaces as $wp)
                <option value="{{ $wp->id }}" {{ (string) old('work_place_id', $schedule->work_place_id ?? '') === (string) $wp->id ? 'selected' : '' }}>{{ $wp->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Shift</label>
        <select name="shift_id" class="form-control" required>
            @foreach($shifts as $s)
                <option value="{{ $s->id }}" {{ (string) old('shift_id', $schedule->shift_id ?? '') === (string) $s->id ? 'selected' : '' }}>{{ $s->code }} - {{ $s->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-4">
        <label>Tanggal</label>
        <input type="date" name="date" class="form-control" value="{{ old('date', optional($schedule->date ?? null)->toDateString()) }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Login Message (opsional)</label>
        <input type="text" name="login_message" class="form-control" value="{{ old('login_message', $schedule->login_message ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>Logout Message (opsional)</label>
        <input type="text" name="logout_message" class="form-control" value="{{ old('logout_message', $schedule->logout_message ?? '') }}">
    </div>
</div>
