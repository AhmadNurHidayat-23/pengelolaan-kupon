@extends('layouts.app')
@section('title', 'Hapus Kupon — Pengelolaan Kupon')
@section('content')
<section class="hero compact"><p class="eyebrow">KONFIRMASI PENGHAPUSAN</p><h1>Hapus Kupon Ini?</h1><p>Periksa kode berikut sebelum melanjutkan.</p></section>
<section class="delete-panel">
    <h2 class="coupon-code">{{ $coupon->code }}</h2>
    <p>Kupon beserta jumlah penggunaannya akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.</p>
    <form action="{{ route('coupons.destroy', $coupon) }}" method="post">
        @csrf @method('delete')
        <a class="button outline" href="{{ route('coupons.show', $coupon) }}">Batal</a>
        <button class="button" type="submit">Ya, hapus kupon</button>
    </form>
</section>
@endsection
