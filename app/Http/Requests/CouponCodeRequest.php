<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponCodeRequest extends FormRequest
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
        return ['code' => ['bail', 'required', 'string', 'regex:/\A[A-Z0-9_-]{3,32}\z/']];
    }

    public function messages(): array
    {
        return ['required' => 'Masukkan kode kupon terlebih dahulu.', 'string' => 'Kode kupon harus berupa teks.',
            'regex' => 'Gunakan 3–32 huruf, angka, tanda hubung, atau garis bawah.'];
    }
}
