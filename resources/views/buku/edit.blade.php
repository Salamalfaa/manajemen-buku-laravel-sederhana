@extends('layouts.app')

@section('title', 'Ubah buku')
@section('page-heading', 'Ubah buku')

@section('content')
    <section class="surface-card form-card">
        <div class="form-card-head">
            <p class="eyebrow">Koleksi perpustakaan</p>
            <h2 class="section-title">Perbarui informasi buku</h2>
            <p class="section-note">Perubahan akan langsung tersimpan ke katalog.</p>
        </div>
        <form method="POST" action="{{ route('buku.update', $buku) }}">
            @csrf
            @method('PUT')
            @include('buku._form', ['submitLabel' => 'Simpan perubahan'])
        </form>
    </section>
@endsection
