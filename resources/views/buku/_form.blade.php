<div class="row g-3">
    <div class="col-12">
        <label class="form-label" for="judul">Judul buku</label>
        <input class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $buku->judul ?? '') }}" maxlength="255" required autofocus placeholder="Contoh: Laskar Pelangi">
        @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="penulis">Penulis</label>
        <input class="form-control @error('penulis') is-invalid @enderror" id="penulis" name="penulis" value="{{ old('penulis', $buku->penulis ?? '') }}" maxlength="255" required placeholder="Nama penulis">
        @error('penulis')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="penerbit">Penerbit</label>
        <input class="form-control @error('penerbit') is-invalid @enderror" id="penerbit" name="penerbit" value="{{ old('penerbit', $buku->penerbit ?? '') }}" maxlength="255" required placeholder="Nama penerbit">
        @error('penerbit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="tahun_terbit">Tahun terbit</label>
        <input class="form-control @error('tahun_terbit') is-invalid @enderror" id="tahun_terbit" type="number" name="tahun_terbit" value="{{ old('tahun_terbit', $buku->tahun_terbit ?? '') }}" min="1000" max="{{ now()->year + 1 }}" required placeholder="{{ now()->year }}">
        @error('tahun_terbit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="form-actions">
    <a class="btn-quiet" href="{{ route('buku.index') }}">Batal</a>
    <button class="btn-accent" type="submit"><i class="bi bi-check2"></i> {{ $submitLabel }}</button>
</div>
