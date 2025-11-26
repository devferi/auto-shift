<div class="form-row">
    <div class="form-group col-md-6">
        <label>Shift</label>
        <select name="shift_id" class="form-control" required>
            @foreach($shifts as $s)
                <option value="{{ $s->id }}" {{ (string) old('shift_id', $rule->shift_id ?? '') === (string) $s->id ? 'selected' : '' }}>{{ $s->code }} - {{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label>Hari</label>
        <select name="day_of_week" class="form-control" required>
            @php($days = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'])
            @foreach($days as $k=>$v)
                <option value="{{ $k }}" {{ (int) old('day_of_week', $rule->day_of_week ?? 1) === (int) $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Mulai</label>
        <input type="time" name="start_time" class="form-control" value="{{ old('start_time', substr($rule->start_time ?? '08:00',0,5)) }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Selesai</label>
        <input type="time" name="end_time" class="form-control" value="{{ old('end_time', substr($rule->end_time ?? '16:00',0,5)) }}" required>
    </div>
</div>
<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label">Aktif</label>
    </div>
</div>
