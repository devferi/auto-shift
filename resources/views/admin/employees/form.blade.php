<div class="form-row">
    <div class="form-group col-md-6">
        <label>Nama</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Kode Orang</label>
        <input type="text" name="person_code" class="form-control" value="{{ old('person_code', $employee->person_code ?? '') }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>No. WhatsApp</label>
        <input type="text" name="wa_number" class="form-control" value="{{ old('wa_number', $employee->wa_number ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Tempat Default</label>
        <select name="default_work_place_id" class="form-control">
            <option value="">-</option>
            @foreach($workPlaces as $wp)
                <option value="{{ $wp->id }}" {{ (string) old('default_work_place_id', $employee->default_work_place_id ?? '') === (string) $wp->id ? 'selected' : '' }}>{{ $wp->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Session Key</label>
        <input type="text" name="session_key" class="form-control" value="{{ old('session_key', $employee->session_key ?? '') }}">
    </div>
    <div class="form-group col-md-6">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $employee->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">Aktif</label>
        </div>
    </div>
</div>
