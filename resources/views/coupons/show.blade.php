@extends('layouts.app')
@section('title', $coupon->code.' — Pengelolaan Kupon')
@section('content')
<section class="hero compact">
    <p class="eyebrow"><a href="{{ route('coupons.index') }}">← KEMBALI KE DAFTAR</a></p>
    <h1>Detail Kupon.</h1>
    <p>Periksa aturan dan penggunaan kupon Anda.</p>
</section>
<section class="detail-panel">
    <div class="detail-primary">
        <p class="eyebrow">KODE KUPON</p>
        <h2 class="detail-code">{{ $coupon->code }}</h2>
        <p class="detail-discount">{{ $coupon->discountLabel() }}</p>
        <span class="status-tag" data-status="{{ $coupon->availability() }}">{{ \App\Models\Coupon::STATUSES[$coupon->availability()] }}</span>
        <p class="detail-reason">{{ $coupon->reason() }}</p>
    </div>
    <dl class="detail-facts">
        <div><dt>Jenis diskon</dt><dd>{{ $coupon->discount_type === 'percentage' ? 'Persentase' : 'Nominal rupiah' }}</dd></div>
        <div><dt>Tanggal mulai</dt><dd>{{ \Illuminate\Support\Carbon::parse($coupon->starts_on)->format('d/m/Y') }}</dd></div>
        <div><dt>Tanggal berakhir</dt><dd>{{ \Illuminate\Support\Carbon::parse($coupon->ends_on)->format('d/m/Y') }}</dd></div>
        <div><dt>Jumlah penggunaan</dt><dd>{{ number_format($coupon->used_count, 0, ',', '.') }} dari {{ number_format($coupon->max_uses, 0, ',', '.') }}</dd></div>
        <div><dt>Sisa penggunaan</dt><dd>{{ number_format(max(0, $coupon->max_uses - $coupon->used_count), 0, ',', '.') }}</dd></div>
        <div><dt>Pengaturan status</dt><dd>{{ $coupon->is_active ? 'Diaktifkan' : 'Dinonaktifkan' }}</dd></div>
    </dl>
</section>
<div class="detail-actions"><a class="text-link" href="{{ route('coupons.confirm-delete', $coupon) }}">Hapus kupon</a><div><a class="button outline" href="{{ route('checks.index') }}">Validasi kupon</a><a class="button" href="{{ route('coupons.edit', $coupon) }}">Edit kupon ↗</a></div></div>
@endsection
