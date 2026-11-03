<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponRequest;
use App\Models\Coupon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:64'], 'status' => ['nullable', Rule::in(array_keys(Coupon::STATUSES))]], [
            'q.string' => 'Pencarian harus berupa teks.', 'q.max' => 'Pencarian maksimal 64 karakter.', 'status.in' => 'Pilihan status tidak tersedia.',
        ]);
        $coupons = Coupon::query()->when($filters['q'] ?? null, fn ($query, $search) => $query->where('code', 'like', '%'.strtoupper(trim($search)).'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->withAvailability($status))
            ->latest('id')->paginate(10)->withQueryString();

        return view('coupons.index', compact('coupons', 'filters'));
    }

    public function create(): View
    {
        $coupon = new Coupon(['discount_type' => 'percentage', 'discount_value' => 10, 'starts_on' => now()->toDateString(),
            'ends_on' => now()->addDays(30)->toDateString(), 'max_uses' => 100, 'is_active' => true]);

        return view('coupons.form', compact('coupon'));
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        try {
            $coupon = Coupon::create($request->validated());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['code' => 'Kode kupon sudah digunakan. Pilih kode lain.']);
        }

        return to_route('coupons.show', $coupon)->with('success', 'Kupon berhasil dibuat.');
    }

    public function show(Coupon $coupon): View
    {
        return view('coupons.show', compact('coupon'));
    }

    public function edit(Coupon $coupon): View
    {
        return view('coupons.form', compact('coupon'));
    }

    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $coupon): void {
                DB::statement('UPDATE coupons SET used_count = used_count WHERE id = ?', [$coupon->id]);
                $coupon->refresh();
                if ((int) $request->validated('max_uses') < $coupon->used_count) {
                    throw ValidationException::withMessages(['max_uses' => 'Batas penggunaan tidak boleh lebih kecil dari jumlah yang sudah digunakan ('.$coupon->used_count.').']);
                }
                $coupon->update($request->validated());
            }, 5);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['code' => 'Kode kupon sudah digunakan. Pilih kode lain.']);
        }

        return to_route('coupons.show', $coupon)->with('success', 'Perubahan kupon berhasil disimpan.');
    }

    public function confirmDelete(Coupon $coupon): View
    {
        return view('coupons.delete', compact('coupon'));
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return to_route('coupons.index')->with('success', 'Kupon berhasil dihapus.');
    }
}
