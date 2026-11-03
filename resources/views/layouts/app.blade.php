<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kelola kode kupon, masa berlaku, diskon, dan batas penggunaan dalam satu tempat. Demo lokal Pengelolaan Kupon.">
    <meta name="theme-color" content="#FFF7ED">
    <title>@yield('title', 'Pengelolaan Kupon')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<header class="shell site-header">
    <a href="{{ route('coupons.index') }}" class="brand">Pengelolaan Kupon<span>.</span></a>
    <nav aria-label="Navigasi utama">
        <a href="{{ route('coupons.index') }}" @if(request()->routeIs('coupons.*')) aria-current="page" @endif>Daftar kupon</a>
        <a href="{{ route('checks.index') }}" @if(request()->routeIs('checks.*')) aria-current="page" @endif>Validasi & simulasi ↗</a>
    </nav>
</header>
<main class="shell">
    @if(session('success'))
        <p class="notice" role="status">{{ session('success') }}</p>
    @endif
    @if($errors->any())
        <div class="notice errors" role="alert">
            <p>Periksa kembali isian Anda.</p>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
<footer class="shell site-footer">
    <p>Pengelolaan Kupon.</p>
    <span>Demo lokal · Tanpa pembayaran</span>
</footer>
</body>
</html>
