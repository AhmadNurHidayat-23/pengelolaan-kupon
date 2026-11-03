@extends('layouts.app')
@section('title', 'Terjadi Gangguan. — Pengelolaan Kupon')
@section('content')
<section class="hero compact"><p class="eyebrow">INFORMASI / 500</p><h1>Terjadi Gangguan.</h1><p>Permintaan belum dapat diproses. Silakan coba kembali.</p></section>
<a class="button" href="{{ route('coupons.index') }}">Kembali ke daftar ↗</a>
@endsection
