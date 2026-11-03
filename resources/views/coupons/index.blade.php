@extends('layouts.app')
@section('content')
<section class="hero">
    <p class="eyebrow">KODE TERTATA. PROMOSI TERJAGA.</p>
    <h1>Kelola Kupon dengan Mudah.</h1>
    <div class="hero-bottom">
        <p>Atur diskon, masa berlaku, dan batas penggunaan dalam satu tempat.</p>
        <a class="button" href="{{ route('coupons.create') }}">Tambah kupon <span aria-hidden="true">↗</span></a>
    </div>
</section>
<form method="get" action="{{ route('coupons.index') }}" class="filter-bar" novalidate>
    <div class="field search-field">
        <label for="q">Cari kode kupon</label>
        <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Contoh: HEMAT20" maxlength="64" type="search">
    </div>
    <div class="field">
        <label for="status">Status kupon</label>
        <select id="status" name="status">
            <option value="">Semua status</option>
            @foreach(\App\Models\Coupon::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="button outline">Terapkan</button>
    @if(!empty($filters['q']) || !empty($filters['status']))
        <a class="text-link" href="{{ route('coupons.index') }}">Hapus filter</a>
    @endif
</form>
<div class="listing-heading">
    <h2>Daftar kupon</h2>
    <span>{{ number_format($coupons->total(), 0, ',', '.') }} kupon{{ !empty($filters['q']) || !empty($filters['status']) ? ' ditemukan' : '' }}</span>
</div>
@if($coupons->isEmpty())
    <section class="empty-state">
        <h2>{{ !empty($filters['q']) || !empty($filters['status']) ? 'Tidak ada kupon yang cocok.' : 'Kupon pertama Anda dimulai di sini.' }}</h2>
        <p>{{ !empty($filters['q']) || !empty($filters['status']) ? 'Coba kode atau status lain untuk memperluas pencarian.' : 'Tambahkan kode, tentukan diskon, lalu atur kapan kupon dapat digunakan.' }}</p>
    </section>
@else
    <div class="table-wrap">
        <table class="coupon-table">
            <thead>
                <tr><th scope="col">KODE KUPON</th><th scope="col">DISKON</th><th scope="col">MASA BERLAKU</th><th scope="col">PENGGUNAAN</th><th scope="col">STATUS</th><th scope="col">TINDAKAN</th></tr>
            </thead>
            <tbody>
                @foreach($coupons as $coupon)
                    <tr>
                        <td data-label="Kode kupon"><a class="coupon-code" href="{{ route('coupons.show', $coupon) }}">{{ $coupon->code }}</a></td>
                        <td data-label="Diskon"><strong class="discount">{{ $coupon->discountLabel() }}</strong><span class="cell-note">{{ $coupon->discount_type === 'percentage' ? 'Persentase' : 'Nominal' }}</span></td>
                        <td data-label="Masa berlaku"><span>{{ \Illuminate\Support\Carbon::parse($coupon->starts_on)->format('d/m/Y') }}</span><span class="cell-note">s.d. {{ \Illuminate\Support\Carbon::parse($coupon->ends_on)->format('d/m/Y') }}</span></td>
                        <td data-label="Penggunaan"><span class="usage">{{ number_format($coupon->used_count, 0, ',', '.') }} <span>/ {{ number_format($coupon->max_uses, 0, ',', '.') }}</span></span><span class="cell-note">{{ number_format(max(0, $coupon->max_uses - $coupon->used_count), 0, ',', '.') }} tersisa</span></td>
                        <td data-label="Status"><span class="status-tag" data-status="{{ $coupon->availability() }}">{{ \App\Models\Coupon::STATUSES[$coupon->availability()] }}</span></td>
                        <td data-label="Tindakan"><a class="text-link" href="{{ route('coupons.edit', $coupon) }}" aria-label="Edit {{ $coupon->code }}">Edit ↗</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $coupons])
@endif
<p class="page-note">Status mengikuti masa berlaku dan sisa penggunaan. Seluruh tanggal menggunakan WIB.</p>
@endsection
