@extends('layouts.app')

@section('title', 'Tambah buku')
@section('page-heading', 'Tambah buku')

@section('content')
    <section class="surface-card form-card">
        <div class="form-card-head">
            <p class="eyebrow">Koleksi perpustakaan</p>
            <h2 class="section-title">Tambahkan judul baru</h2>
            <p class="section-note">Lengkapi detail buku agar mudah ditemukan dalam katalog.</p>
        </div>
        <form method="POST" action="{{ route('buku.store') }}">
            @csrf
            @include('buku._form', ['submitLabel' => 'Simpan buku'])
        </form>
    </section>
@endsection
