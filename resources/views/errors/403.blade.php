@extends('layouts.app')
@section('title', 'Akses Lokal Saja. — Pengelolaan Kupon')
@section('content')
<section class="hero compact"><p class="eyebrow">INFORMASI / 403</p><h1>Akses Lokal Saja.</h1><p>Aplikasi demo tanpa login ini hanya tersedia melalui localhost dalam lingkungan lokal. Tambahkan autentikasi dan pembatasan akses sebelum dipublikasikan.</p></section>
<a class="button" href="{{ route('coupons.index') }}">Kembali ke daftar ↗</a>
@endsection
