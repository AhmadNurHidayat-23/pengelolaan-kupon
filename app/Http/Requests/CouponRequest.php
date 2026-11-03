<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->code)) {
            $this->merge(['code' => strtoupper(trim($this->code))]);
        }
    }

    public function rules(): array
    {
        return [
            'code' => ['bail', 'required', 'string', 'regex:/\A[A-Z0-9_-]{3,32}\z/', Rule::unique('coupons', 'code')->ignore($this->route('coupon'))],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'integer', 'min:1', 'max:'.($this->discount_type === 'percentage' ? 100 : 1000000000)],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'max_uses' => ['required', 'integer', 'min:1', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
            'used_count' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.', 'string' => ':attribute harus berupa teks.',
            'code.regex' => 'Kode harus terdiri dari 3–32 huruf, angka, tanda hubung, atau garis bawah.',
            'code.unique' => 'Kode kupon sudah digunakan. Pilih kode lain.',
            'in' => 'Pilihan :attribute tidak tersedia.', 'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ':attribute minimal :min.', 'max' => ':attribute maksimal :max.',
            'date_format' => ':attribute harus berupa tanggal yang valid.',
            'ends_on.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
            'boolean' => 'Status aktif tidak valid.', 'prohibited' => 'Jumlah penggunaan hanya dapat diubah melalui simulasi.',
        ];
    }

    public function attributes(): array
    {
        return ['code' => 'Kode kupon', 'discount_type' => 'Jenis diskon', 'discount_value' => 'Nilai diskon',
            'starts_on' => 'Tanggal mulai', 'ends_on' => 'Tanggal berakhir', 'max_uses' => 'Batas penggunaan', 'is_active' => 'Status aktif'];
    }
}
