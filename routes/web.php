<?php

use App\Http\Controllers\CouponCheckController;
use App\Http\Controllers\CouponController;
use App\Http\Middleware\LocalDemo;
use Illuminate\Support\Facades\Route;

Route::middleware(LocalDemo::class)->group(function (): void {
    Route::get('/', [CouponController::class, 'index'])->name('coupons.index');
    Route::get('/periksa', [CouponCheckController::class, 'index'])->name('checks.index');
    Route::post('/periksa', [CouponCheckController::class, 'check'])->name('checks.check');
    Route::post('/simulasi', [CouponCheckController::class, 'redeem'])->middleware('throttle:60,1')->name('checks.redeem');
    Route::get('/kupon/{coupon}/hapus', [CouponController::class, 'confirmDelete'])->name('coupons.confirm-delete');
    Route::resource('kupon', CouponController::class)->parameters(['kupon' => 'coupon'])->names('coupons')->except('index');
});
