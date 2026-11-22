<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterPatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
                'unique:user,user_nik',
            ],
            'nama' => [
                'required',
                'string',
                'min:2',
                'max:150',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                'unique:user,user_email',
            ],
            'nowa' => [
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^(\+62|62|0)[0-9]{8,13}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'max:100',
            ],
            'password_confirmation' => [
                'nullable',
                'same:password',
            ],
            'alamat' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'jeniskelamin' => [
                'nullable',
                'in:Laki-laki,Perempuan',
            ],
            'tgllahir' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'nobpjs' => [
                'nullable',
                'digits:13',
                'unique:pasien,pasien_nobpjs',
            ],
            'goldarah' => [
                'nullable',
                'in:A,B,AB,O',
            ],
            'alergiobat' => [
                'nullable',
                'string',
                'max:255',
            ],
            'poli_id' => [
                'nullable',
                'integer',
                'exists:poli,poli_id',
            ],
        ];
    }

    /**
     * Custom validation error messages in Indonesian.
     */
    public function messages(): array
    {
        return [
            'nik.required' => 'NIK (Nomor Induk Kependudukan) wajib diisi.',
            'nik.regex' => 'NIK harus berupa 16 digit angka sesuai KTP.',
            'nik.unique' => 'NIK sudah terdaftar dalam sistem KLINIKU. Silakan periksa kembali atau gunakan opsi Login.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama.min' => 'Nama lengkap minimal terdiri dari 2 karakter.',
            'nama.max' => 'Nama lengkap maksimal 150 karakter.',
            'email.required' => 'Alamat email wajib diisi untuk verifikasi OTP.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah terdaftar dalam sistem KLINIKU.',
            'nowa.required' => 'Nomor WhatsApp wajib diisi.',
            'nowa.regex' => 'Format nomor WhatsApp tidak valid. Gunakan format yang valid seperti 081234567890.',
            'nowa.min' => 'Nomor WhatsApp minimal 10 digit.',
            'nowa.max' => 'Nomor WhatsApp maksimal 15 digit.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'password_confirmation.same' => 'Konfirmasi password tidak cocok dengan password yang dimasukkan.',
            'alamat.required' => 'Alamat domisili wajib diisi.',
            'alamat.min' => 'Alamat domisili minimal 3 karakter.',
            'alamat.max' => 'Alamat domisili maksimal 255 karakter.',
            'jeniskelamin.in' => 'Jenis kelamin harus salah satu dari: Laki-laki atau Perempuan.',
            'tgllahir.date_format' => 'Format tanggal lahir harus YYYY-MM-DD.',
            'nobpjs.digits' => 'Nomor BPJS / KIS harus terdiri dari tepat 13 digit angka.',
            'nobpjs.unique' => 'Nomor BPJS / KIS sudah terdaftar untuk pasien lain.',
            'goldarah.in' => 'Golongan darah harus A, B, AB, atau O.',
            'poli_id.exists' => 'Poliklinik yang dipilih tidak ditemukan dalam sistem.',
        ];
    }

    /**
     * Handle failed validation response.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi format atau duplikasi data gagal. Silakan periksa kembali formulir pendaftaran.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
