<div class="form-row">
    <div class="form-group col-md-4">
        <label>Kode</label>
        <input type="text" name="code" class="form-control" value="{{ old('code', $shift->code ?? '') }}" required>
    </div>
    <div class="form-group col-md-8">
        <label>Nama</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $shift->name ?? '') }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Random Before (menit)</label>
        <input type="number" min="0" max="120" name="random_before_minutes" class="form-control" value="{{ old('random_before_minutes', $shift->random_before_minutes ?? config('attendance.random_before_default')) }}">
    </div>
    <div class="form-group col-md-6">
        <label>Random After (menit)</label>
        <input type="number" min="0" max="120" name="random_after_minutes" class="form-control" value="{{ old('random_after_minutes', $shift->random_after_minutes ?? config('attendance.random_after_default')) }}">
    </div>
</div>
<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $shift->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label">Aktif</label>
    </div>
</div>
