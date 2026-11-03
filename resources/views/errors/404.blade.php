@extends('layouts.app')
@section('title', 'Halaman Tidak Ditemukan. — Pengelolaan Kupon')
@section('content')
<section class="hero compact"><p class="eyebrow">INFORMASI / 404</p><h1>Halaman Tidak Ditemukan.</h1><p>Kupon atau halaman yang Anda cari tidak tersedia.</p></section>
<a class="button" href="{{ route('coupons.index') }}">Kembali ke daftar ↗</a>
@endsection
