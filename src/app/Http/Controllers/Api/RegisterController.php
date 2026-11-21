<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPatientRequest;
use App\Models\Pasien;
use App\Models\Poli;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Handle patient registration.
     */
    public function register(RegisterPatientRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $nikDetails = self::parseNikDetails($validated['nik']);

        // Determine gender and birth date (use input if provided, otherwise auto-derive from NIK)
        $gender = $validated['jeniskelamin'] ?? ($nikDetails['gender'] ?? 'Laki-laki');
        $birthDate = $validated['tgllahir'] ?? ($nikDetails['birth_date'] ?? date('Y-m-d'));

        // Normalize WhatsApp phone number
        $nowa = preg_replace('/[^0-9]/', '', $validated['nowa']);
        if (str_starts_with($nowa, '62')) {
            $nowa = '0' . substr($nowa, 2);
        }

        try {
            $result = DB::transaction(function () use ($validated, $gender, $birthDate, $nowa) {
                // Generate next user_id (USR-PAS-XXXX)
                $latestUser = User::where('user_id', 'LIKE', 'USR-PAS-%')
                    ->orderByRaw('CAST(SUBSTRING(user_id, 9) AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $nextUserSeq = 1;
                if ($latestUser) {
                    $nextUserSeq = ((int) substr($latestUser->user_id, 8)) + 1;
                }
                $userId = sprintf('USR-PAS-%04d', $nextUserSeq);

                // Generate next Medical Record number (RM-YYYY-XXXX)
                $currentYear = date('Y');
                $latestPasien = Pasien::where('pasien_norm', 'LIKE', "RM-{$currentYear}-%")
                    ->orderByRaw('CAST(SUBSTRING(pasien_norm, 9) AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $nextRmSeq = 1;
                if ($latestPasien) {
                    $nextRmSeq = ((int) substr($latestPasien->pasien_norm, 8)) + 1;
                }
                $norm = sprintf('RM-%s-%04d', $currentYear, $nextRmSeq);

                // Create master user record
                $user = User::create([
                    'user_id' => $userId,
                    'user_role' => 'pasien',
                    'user_nama' => trim($validated['nama']),
                    'user_nik' => $validated['nik'],
                    'user_nowa' => $nowa,
                    'user_email' => null,
                    'user_password' => Hash::make($validated['password']),
                    'user_status' => 'aktif',
                ]);

                // Create patient profile record
                $pasien = Pasien::create([
                    'pasien_user_id' => $userId,
                    'pasien_role' => 'pasien',
                    'pasien_norm' => $norm,
                    'pasien_jeniskelamin' => $gender,
                    'pasien_tgllahir' => $birthDate,
                    'pasien_alamat' => trim($validated['alamat']),
                    'pasien_nobpjs' => !empty($validated['nobpjs']) ? $validated['nobpjs'] : null,
                    'pasien_goldarah' => !empty($validated['goldarah']) ? $validated['goldarah'] : null,
                    'pasien_alergiobat' => !empty($validated['alergiobat']) ? trim($validated['alergiobat']) : null,
                    'pasien_poli_id' => !empty($validated['poli_id']) ? (int) $validated['poli_id'] : null,
                ]);

                return [$user, $pasien];
            });

            /** @var User $user */
            /** @var Pasien $pasien */
            [$user, $pasien] = $result;
            $pasien->load('poli');

            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran akun pasien berhasil! Nomor Rekam Medis (RM) telah dibuat. Silakan melanjutkan ke halaman login.',
                'data' => [
                    'user_id' => $user->user_id,
                    'user_role' => $user->user_role,
                    'user_nama' => $user->user_nama,
                    'user_nik' => $user->user_nik,
                    'user_nowa' => $user->user_nowa,
                    'pasien_norm' => $pasien->pasien_norm,
                    'pasien_jeniskelamin' => $pasien->pasien_jeniskelamin,
                    'pasien_tgllahir' => $pasien->pasien_tgllahir,
                    'pasien_alamat' => $pasien->pasien_alamat,
                    'pasien_nobpjs' => $pasien->pasien_nobpjs,
                    'pasien_goldarah' => $pasien->pasien_goldarah,
                    'pasien_alergiobat' => $pasien->pasien_alergiobat,
                    'poli_nama' => $pasien->poli?->poli_nama,
                    'created_at' => $user->user_createdat,
                ],
                'next_step' => [
                    'step' => 4,
                    'action' => 'login',
                    'endpoint' => '/api/v1/login',
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses registrasi. Silakan coba kembali.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check if NIK is valid and not already registered (live format & duplicate check).
     */
    public function checkNik(Request $request): JsonResponse
    {
        $nik = $request->input('nik') ?? $request->query('nik');

        $validator = Validator::make(['nik' => $nik], [
            'nik' => ['required', 'string', 'regex:/^[0-9]{16}$/'],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => $validator->errors()->first('nik'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $isDuplicate = User::where('user_nik', $nik)->exists();

        if ($isDuplicate) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'NIK sudah terdaftar dalam sistem KLINIKU. Silakan langsung login dengan NIK Anda.',
            ], 409);
        }

        $details = self::parseNikDetails($nik);

        return response()->json([
            'success' => true,
            'available' => true,
            'message' => 'NIK valid dan belum terdaftar. Anda dapat melanjutkan pendaftaran.',
            'nik_info' => $details,
        ], 200);
    }

    /**
     * Get polyclinic master list for registration dropdown.
     */
    public function getPoliList(): JsonResponse
    {
        $polis = Poli::select('poli_id', 'poli_nama', 'poli_keterangan')->orderBy('poli_id')->get();

        return response()->json([
            'success' => true,
            'data' => $polis,
        ]);
    }

    /**
     * Helper to decode birth date and gender from Indonesian NIK.
     */
    public static function parseNikDetails(string $nik): array
    {
        if (strlen($nik) !== 16 || !ctype_digit($nik)) {
            return ['valid' => false];
        }

        $rawDay = (int) substr($nik, 6, 2);
        $month = (int) substr($nik, 8, 2);
        $yearShort = (int) substr($nik, 10, 2);

        $gender = ($rawDay > 40) ? 'Perempuan' : 'Laki-laki';
        $day = ($rawDay > 40) ? ($rawDay - 40) : $rawDay;

        $currentYearShort = (int) date('y');
        $fullYear = ($yearShort <= $currentYearShort) ? (2000 + $yearShort) : (1900 + $yearShort);

        $birthDate = null;
        $age = null;
        if (checkdate($month, $day, $fullYear)) {
            $birthDate = sprintf('%04d-%02d-%02d', $fullYear, $month, $day);
            $age = Carbon::parse($birthDate)->age;
        }

        return [
            'valid' => true,
            'gender' => $gender,
            'birth_date' => $birthDate,
            'age' => $age,
        ];
    }
}
