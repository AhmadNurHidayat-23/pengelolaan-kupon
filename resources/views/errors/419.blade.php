@extends('layouts.app')
@section('title', 'Sesi Telah Berakhir. — Pengelolaan Kupon')
@section('content')
<section class="hero compact"><p class="eyebrow">INFORMASI / 419</p><h1>Sesi Telah Berakhir.</h1><p>Muat ulang halaman, lalu kirim kembali formulir Anda.</p></section>
<a class="button" href="{{ route('coupons.index') }}">Kembali ke daftar ↗</a>
@endsection
