<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs before validation to support multiple parameter aliases for ReCaptcha.
     */
    protected function prepareForValidation(): void
    {
        $token = $this->input('recaptcha_token') 
            ?? $this->input('g-recaptcha-response') 
            ?? $this->input('captcha_token')
            ?? $this->input('recaptcha');

        if ($token !== null) {
            $this->merge(['recaptcha_token' => $token]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => [
                'required',
                'string',
                'regex:/^[0-9]{16}$/',
            ],
            'password' => [
                'required',
                'string',
            ],
            'recaptcha_token' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     */
    public function messages(): array
    {
        return [
            'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.regex' => 'NIK harus berupa 16 digit angka sesuai KTP.',
            'password.required' => 'Kata sandi (password) wajib diisi.',
            'recaptcha_token.required' => 'Verifikasi reCAPTCHA ("Saya bukan robot") wajib dicentang.',
        ];
    }

    /**
     * Handle failed validation response.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Format input data login tidak valid. Silakan periksa kembali NIK, password, dan centang reCAPTCHA.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
