<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponCodeRequest;
use App\Models\Coupon;
use App\Services\CouponRedeemer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CouponCheckController extends Controller
{
    public function index(): View
    {
        $code = session('simulation.code', '');
        $coupon = $code ? Coupon::where('code', $code)->first() : null;

        return view('checks.index', ['coupon' => $coupon, 'checked' => $code !== '', 'code' => $code]);
    }

    public function check(CouponCodeRequest $request): View
    {
        $code = $request->validated('code');
        $coupon = Coupon::where('code', $code)->first();

        return view('checks.index', ['coupon' => $coupon, 'checked' => true, 'code' => $code]);
    }

    public function redeem(CouponCodeRequest $request, CouponRedeemer $redeemer): RedirectResponse
    {
        $result = $redeemer->redeem($request->validated('code'));

        return to_route('checks.index')->with('simulation', [
            'success' => $result['success'], 'message' => $result['message'], 'code' => $request->validated('code'),
        ]);
    }
}
