@extends('layouts.app')

@section('title', 'Koleksi Buku')
@section('page-heading', 'Koleksi buku')

@section('content')
    <section class="collection-intro">
        <div class="collection-intro-copy">
            <span class="analytics-eyebrow"><i class="bi bi-journal-bookmark"></i> Perpustakaan Anda</span>
            <h2>Temukan bacaan berikutnya.</h2>
            <p>Jelajahi koleksi, temukan penulis baru, dan simpan semua cerita favorit dalam satu katalog.</p>
        </div>
        @can('tambah buku')
            <a class="btn-accent collection-add-button" href="{{ route('buku.create') }}"><i class="bi bi-plus-lg"></i> Tambah buku</a>
        @endcan
    </section>

    <section class="collection-summary-row" aria-label="Ringkasan koleksi buku">
        <div class="collection-summary-item"><span class="collection-summary-icon"><i class="bi bi-bookshelf"></i></span><span><strong>{{ number_format($totalBuku, 0, ',', '.') }}</strong><small>judul terkumpul</small></span></div>
        <span class="collection-summary-divider" aria-hidden="true"></span>
        <div class="collection-summary-item"><span class="collection-summary-icon collection-icon-purple"><i class="bi bi-pen"></i></span><span><strong>{{ number_format($totalPenulis, 0, ',', '.') }}</strong><small>penulis berbagi karya</small></span></div>
        <span class="collection-summary-divider" aria-hidden="true"></span>
        <div class="collection-summary-item"><span class="collection-summary-icon collection-icon-green"><i class="bi bi-calendar2-heart"></i></span><span><strong>{{ $bukuTerbaru ?: '—' }}</strong><small>tahun terbit terbaru</small></span></div>
    </section>

    <section class="surface-card catalog-card" id="catalog">
        <div class="catalog-head collection-catalog-head">
            <div class="collection-heading-copy">
                <span class="analytics-eyebrow"><i class="bi bi-grid"></i> Katalog perpustakaan</span>
                <h2 class="section-title">Semua buku <span class="catalog-result-count">{{ number_format($totalHasil, 0, ',', '.') }}</span></h2>
                <p class="section-note">{{ $search || $year ? 'Hasil yang sesuai dengan filter Anda.' : 'Temukan cerita baru dari pilihan koleksi yang tersedia.' }}</p>
            </div>
            <form class="catalog-filter-form" method="GET" action="{{ route('buku.index') }}" role="search">
                <label class="catalog-search-field">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" name="search" value="{{ $search }}" placeholder="Judul, penulis, penerbit..." aria-label="Cari judul, penulis, atau penerbit">
                    @if($search)<a href="{{ route('buku.index', array_filter(['year' => $year, 'sort' => $sort])) }}" class="catalog-clear-search" aria-label="Hapus kata pencarian"><i class="bi bi-x-circle-fill"></i></a>@endif
                </label>
                <label class="visually-hidden" for="catalogYear">Filter tahun terbit</label>
                <select class="form-select catalog-filter-select" name="year" id="catalogYear" aria-label="Filter tahun terbit">
                    <option value="">Semua tahun</option>
                    @foreach($tahunTerbit as $tahun)
                        <option value="{{ $tahun }}" @selected((string) $year === (string) $tahun)>{{ $tahun }}</option>
                    @endforeach
                </select>
                <label class="visually-hidden" for="catalogSort">Urutkan koleksi</label>
                <select class="form-select catalog-filter-select" name="sort" id="catalogSort" aria-label="Urutkan koleksi">
                    <option value="newest" @selected($sort === 'newest')>Terbaru ditambahkan</option>
                    <option value="oldest" @selected($sort === 'oldest')>Terlama ditambahkan</option>
                    <option value="title" @selected($sort === 'title')>Judul A–Z</option>
                    <option value="author" @selected($sort === 'author')>Penulis A–Z</option>
                    <option value="year" @selected($sort === 'year')>Tahun terbit terbaru</option>
                </select>
                <button class="btn-accent catalog-filter-submit" type="submit"><i class="bi bi-funnel"></i><span>Terapkan</span></button>
            </form>
        </div>

        @if($bukus->isNotEmpty())
            <div class="book-card-grid">
                @foreach($bukus as $buku)
                    @php($coverColors = ['#7b84ee', '#c188d9', '#5cae99', '#d59468', '#7397cf'])
                    <article class="book-card">
                        <div class="book-card-cover" style="--book-cover: {{ $coverColors[($buku->id - 1) % count($coverColors)] }}">
                            <span class="book-cover-glow"></span>
                            <span class="book-cover-mark"><i class="bi bi-book-half"></i></span>
                            <span class="book-cover-year">{{ $buku->tahun_terbit }}</span>
                            <span class="book-cover-spine"></span>
                        </div>
                        <div class="book-card-content">
                            <span class="book-card-publisher">{{ $buku->penerbit }}</span>
                            <h3 class="book-card-title">{{ $buku->judul }}</h3>
                            <p class="book-card-author"><i class="bi bi-pen"></i> {{ $buku->penulis }}</p>
                            <div class="book-card-footer">
                                <span class="book-card-year"><i class="bi bi-calendar3"></i> {{ $buku->tahun_terbit }}</span>
                                @canany(['edit buku', 'hapus buku'])
                                    <div class="book-card-actions">
                                        @can('edit buku')
                                            <a class="icon-button" href="{{ route('buku.edit', $buku) }}" aria-label="Ubah {{ $buku->judul }}" title="Ubah buku"><i class="bi bi-pencil"></i></a>
                                        @endcan
                                        @can('hapus buku')
                                            <form method="POST" action="{{ route('buku.destroy', $buku) }}" data-delete-book>
                                                @csrf
                                                @method('DELETE')
                                                <button class="icon-button book-delete-button" type="submit" aria-label="Hapus {{ $buku->judul }}" title="Hapus buku"><i class="bi bi-trash3"></i></button>
                                            </form>
                                        @endcan
                                    </div>
                                @else
                                    <span class="book-readonly-label"><i class="bi bi-eye"></i> Siap dibaca</span>
                                @endcanany
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            @if($bukus->hasPages())
                <div class="pagination-footer collection-pagination">
                    <span>Menampilkan {{ number_format($bukus->firstItem(), 0, ',', '.') }}–{{ number_format($bukus->lastItem(), 0, ',', '.') }} dari {{ number_format($bukus->total(), 0, ',', '.') }} buku</span>
                    {{ $bukus->links() }}
                </div>
            @endif
        @else
            <div class="collection-empty-state">
                <span class="empty-icon"><i class="bi bi-journal-x"></i></span>
                <h3>{{ $search || $year ? 'Buku belum ditemukan' : 'Koleksi masih kosong' }}</h3>
                <p>
                    @if($search || $year)
                        Coba ubah kata pencarian atau pilih tahun terbit yang berbeda.
                    @else
                        Tambahkan koleksi buku pertama untuk mulai mengisi perpustakaan.
                    @endif
                </p>
                <div class="collection-empty-actions">
                    @if($search || $year)
                        <a class="btn-quiet" href="{{ route('buku.index') }}"><i class="bi bi-arrow-counterclockwise"></i> Hapus filter</a>
                    @endif
                    @can('tambah buku')
                        @unless($search || $year)
                            <a class="btn-accent" href="{{ route('buku.create') }}"><i class="bi bi-plus-lg"></i> Tambah buku pertama</a>
                        @endunless
                    @endcan
                </div>
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('#catalogYear, #catalogSort').forEach((select) => {
            select.addEventListener('change', () => select.form.requestSubmit());
        });
    </script>
@endpush
