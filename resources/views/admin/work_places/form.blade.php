<div class="form-row">
    <div class="form-group col-md-4">
        <label>Kode</label>
        <input type="text" name="code" class="form-control" value="{{ old('code', $workPlace->code ?? '') }}" required>
    </div>
    <div class="form-group col-md-8">
        <label>Nama</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $workPlace->name ?? '') }}" required>
    </div>
</div>
<div class="form-group">
    <label>Deskripsi</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $workPlace->description ?? '') }}</textarea>
    </div>
<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $workPlace->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label">Aktif</label>
    </div>
</div>
