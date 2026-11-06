{{-- Pilihan gelombang yang dikelola. Butuh $pilihan (id + label) dan $dipilih. Mengirim ?gelombang=ID lewat GET. --}}
<form method="GET" class="gel-pilih">
    <label for="gel-pilih">Gelombang</label>
    <select class="form-control" id="gel-pilih" name="gelombang" onchange="this.form.submit()">
        @foreach ($pilihan as $o)
            <option value="{{ $o['id'] }}" {{ (int) $dipilih === $o['id'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
        @endforeach
    </select>
    <noscript><button class="btn btn-sm btn-outline-light" type="submit">Tampilkan</button></noscript>
</form>
