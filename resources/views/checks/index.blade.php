@extends('layouts.app')
@section('title', 'Validasi & Simulasi — Pengelolaan Kupon')
@section('content')
<section class="hero compact">
    <p class="eyebrow">VALIDASI & SIMULASI</p>
    <h1>Periksa Kupon Anda.</h1>
    <p>Pastikan kode berlaku sebelum mencoba penggunaannya.</p>
</section>
@if(session('simulation'))
    <div class="notice" role="status"><strong>{{ session('simulation.success') ? 'Simulasi berhasil.' : 'Simulasi ditolak.' }}</strong><p>{{ session('simulation.message') }}</p></div>
@endif
<section class="check-grid">
    <div class="check-input">
        <p class="eyebrow">01 / MASUKKAN KODE</p>
        <h2>Satu kode, satu pemeriksaan.</h2>
        <p>Periksa status, masa berlaku, dan sisa penggunaan kupon.</p>
        <form method="post" action="{{ route('checks.check') }}" novalidate>
            @csrf
            <div class="field"><label for="code">Kode kupon</label><input id="code" name="code" value="{{ old('code', $code) }}" maxlength="32" placeholder="Contoh: HEMAT20" autocomplete="off" autocapitalize="characters" spellcheck="false" @error('code') aria-invalid="true" @enderror></div>
            <button class="button full" type="submit">Periksa Kupon ↗</button>
        </form>
        <p class="field-help">Pemeriksaan tidak menambah jumlah penggunaan.</p>
    </div>
    <div class="check-result" aria-live="polite">
        <p class="eyebrow">02 / HASIL PEMERIKSAAN</p>
        @if($checked)
            <span class="status-tag" data-status="{{ $coupon?->availability() ?? 'inactive' }}">{{ $coupon && $coupon->availability() === 'valid' ? 'Valid' : 'Tidak valid' }}</span>
            <h2 class="result-code">{{ $code }}</h2>
            <p class="result-reason">{{ $coupon?->reason() ?? 'Kode kupon tidak ditemukan. Periksa kembali kode yang Anda masukkan.' }}</p>
            @if($coupon)
                <dl class="result-facts">
                    <div><dt>Diskon</dt><dd>{{ $coupon->discountLabel() }}</dd></div>
                    <div><dt>Digunakan</dt><dd>{{ $coupon->used_count }} / {{ $coupon->max_uses }}</dd></div>
                    <div><dt>Masa berlaku</dt><dd>{{ \Illuminate\Support\Carbon::parse($coupon->starts_on)->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($coupon->ends_on)->format('d/m/Y') }}</dd></div>
                </dl>
                <form method="post" action="{{ route('checks.redeem') }}">
                    @csrf
                    <input type="hidden" name="code" value="{{ $code }}">
                    <button class="button full" type="submit" @disabled($coupon->availability() !== 'valid')>Simulasikan penggunaan ↗</button>
                </form>
                <p class="field-help">Setiap simulasi berhasil dihitung sebagai satu penggunaan. Ketersediaan diperiksa kembali saat diproses.</p>
            @endif
        @else
            <h2>Hasil akan tampil di sini.</h2>
            <p class="result-reason">Masukkan kode kupon untuk melihat apakah kupon dapat digunakan.</p>
            <div class="check-explanation"><p>Validasi memeriksa:</p><ol><li>Status aktif kupon</li><li>Tanggal mulai dan berakhir</li><li>Sisa batas penggunaan</li></ol></div>
        @endif
    </div>
</section>
<p class="page-note">Simulasi lokal tanpa transaksi pembayaran. Seluruh tanggal menggunakan WIB.</p>
@endsection
