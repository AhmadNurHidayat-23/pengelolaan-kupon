@extends('layouts.app')
@section('title', ($coupon->exists ? 'Edit' : 'Tambah').' Kupon — Pengelolaan Kupon')
@section('content')
<section class="hero compact">
    <p class="eyebrow"><a href="{{ route('coupons.index') }}">← KEMBALI KE DAFTAR</a></p>
    <h1>{{ $coupon->exists ? 'Edit Kupon Anda.' : 'Buat Kupon Baru.' }}</h1>
    <p>Detail yang jelas untuk promosi yang terukur.</p>
</section>
<form action="{{ $coupon->exists ? route('coupons.update', $coupon) : route('coupons.store') }}" method="post" class="editor-form" novalidate>
    @csrf
    @if($coupon->exists) @method('put') @endif
    <section class="form-section">
        <div class="section-label"><p class="eyebrow">01 / IDENTITAS & DISKON</p><h2>Tentukan nilai kupon.</h2><p>Gunakan kode yang singkat dan mudah diingat.</p></div>
        <div class="form-fields">
            <div class="field wide">
                <label for="code">Kode kupon <span>Wajib</span></label>
                <input id="code" name="code" value="{{ old('code', $coupon->code) }}" maxlength="32" placeholder="Contoh: HEMAT20" autocomplete="off" autocapitalize="characters" spellcheck="false" aria-describedby="code-help" @error('code') aria-invalid="true" @enderror>
                <p class="field-help" id="code-help">3–32 huruf, angka, tanda hubung, atau garis bawah. Huruf disimpan sebagai kapital.</p>
            </div>
            <div class="field">
                <label for="discount_type">Jenis diskon</label>
                <select id="discount_type" name="discount_type" data-discount-type>
                    <option value="percentage" @selected(old('discount_type', $coupon->discount_type) === 'percentage')>Persentase (%)</option>
                    <option value="fixed" @selected(old('discount_type', $coupon->discount_type) === 'fixed')>Nominal (Rp)</option>
                </select>
            </div>
            <div class="field">
                <label for="discount_value">Nilai diskon</label>
                <input type="number" id="discount_value" name="discount_value" value="{{ old('discount_value', $coupon->discount_value) }}" min="1" step="1" inputmode="numeric" aria-describedby="value-help" data-discount-value @error('discount_value') aria-invalid="true" @enderror>
                <p class="field-help" id="value-help" data-discount-help>Persentase 1–100; nominal dalam rupiah utuh.</p>
            </div>
        </div>
    </section>
    <section class="form-section">
        <div class="section-label"><p class="eyebrow">02 / ATURAN PENGGUNAAN</p><h2>Atur masa berlaku.</h2><p>Tanggal mulai dan berakhir termasuk hari penggunaan.</p></div>
        <div class="form-fields">
            <div class="field"><label for="starts_on">Tanggal mulai</label><input type="date" id="starts_on" name="starts_on" value="{{ old('starts_on', $coupon->starts_on) }}" @error('starts_on') aria-invalid="true" @enderror></div>
            <div class="field"><label for="ends_on">Tanggal berakhir</label><input type="date" id="ends_on" name="ends_on" value="{{ old('ends_on', $coupon->ends_on) }}" @error('ends_on') aria-invalid="true" @enderror></div>
            <div class="field wide">
                <label for="max_uses">Batas maksimal penggunaan</label>
                <input type="number" id="max_uses" name="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" min="{{ max(1, $coupon->used_count ?? 0) }}" max="1000000" step="1" inputmode="numeric" aria-describedby="limit-help" @error('max_uses') aria-invalid="true" @enderror>
                <p class="field-help" id="limit-help">1–1.000.000 penggunaan.{{ $coupon->exists ? ' Sudah digunakan '.$coupon->used_count.' kali; jumlah ini tidak dapat diedit.' : '' }}</p>
            </div>
            <div class="field wide active-field">
                <input type="hidden" name="is_active" value="0">
                <label class="checkbox-label" for="is_active"><input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active))>Aktifkan kupon</label>
                <p class="field-help">Kupon tetap mengikuti masa berlaku dan batas penggunaan.</p>
            </div>
        </div>
    </section>
    <div class="form-actions"><p>Semua isian wajib diisi.</p><div><a class="text-link" href="{{ $coupon->exists ? route('coupons.show', $coupon) : route('coupons.index') }}">Batal</a><button class="button" type="submit">{{ $coupon->exists ? 'Simpan perubahan' : 'Simpan kupon' }} ↗</button></div></div>
</form>
@endsection
