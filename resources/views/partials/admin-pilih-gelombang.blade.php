{{-- Pilihan gelombang yang dikelola. Butuh $pilihan (id + label) dan $dipilih. Mengirim ?gelombang=ID lewat GET. --}}
<form method="GET" class="gel-pilih" style="display:flex;align-items:center;gap:8px">
    <label for="gel-pilih" style="font-size:.82rem;color:var(--gray-500);white-space:nowrap">Gelombang</label>
    <select class="form-control" id="gel-pilih" name="gelombang" style="width:auto;min-width:260px" onchange="this.form.submit()">
        @foreach ($pilihan as $o)
            <option value="{{ $o['id'] }}" {{ (int) $dipilih === $o['id'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
        @endforeach
    </select>
    <noscript><button class="btn btn-sm btn-outline-light" type="submit">Tampilkan</button></noscript>
</form>
