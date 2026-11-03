@extends('layouts.app')
@section('title', 'Terlalu Banyak Permintaan. — Pengelolaan Kupon')
@section('content')
<section class="hero compact"><p class="eyebrow">INFORMASI / 429</p><h1>Terlalu Banyak Permintaan.</h1><p>Tunggu sejenak sebelum mencoba kembali.</p></section>
<a class="button" href="{{ route('coupons.index') }}">Kembali ke daftar ↗</a>
@endsection
