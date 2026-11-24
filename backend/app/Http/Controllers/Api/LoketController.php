<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\QueueCalledMail;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\User;
use App\Services\QuotaEngineService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class LoketController extends Controller
{
    protected QuotaEngineService $quotaEngine;

    public function __construct(QuotaEngineService $quotaEngine)
    {
        $this->quotaEngine = $quotaEngine;
    }

    /**
     * QR Check-in di Loket Fisik (US-08).
     * Mengubah status antrean online dari 'menunggu_checkin' menjadi 'menunggu'.
     */
    public function checkInQr(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
        ], [
            'qr_code.required' => 'Kode QR e-tiket wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $qrCode = trim($request->qr_code);

        $antrean = Antrean::with(['pasien.user', 'poli', 'dokter.user'])
            ->where('antrean_qr_code', $qrCode)
            ->first();

        if (!$antrean) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode QR e-tiket tidak valid atau tidak terdaftar di sistem.',
            ], 404);
        }

        $today = Carbon::today()->toDateString();
        $antreanDate = $antrean->antrean_tanggal->format('Y-m-d');

        if ($antreanDate !== $today) {
            return response()->json([
                'status' => 'error',
                'message' => "Check-in gagal. Jadwal kunjungan Anda adalah tanggal {$antreanDate}, sedangkan hari ini adalah {$today}.",
            ], 422);
        }

        if ($antrean->antrean_status === 'batal') {
            return response()->json([
                'status' => 'error',
                'message' => 'Antrean ini telah dibatalkan sebelumnya.',
            ], 422);
        }

        if ($antrean->antrean_status !== 'menunggu_checkin') {
            return response()->json([
                'status' => 'error',
                'message' => "Antrean {$antrean->antrean_nomor} sudah melakukan check-in sebelumnya (Status: {$antrean->antrean_status}).",
                'data' => [
                    'antrean_id' => $antrean->antrean_id,
                    'antrean_nomor' => $antrean->antrean_nomor,
                    'antrean_status' => $antrean->antrean_status,
                    'waktu_checkin' => $antrean->antrean_waktu_checkin?->format('H:i:s'),
                ],
            ], 422);
        }

        $antrean->antrean_status = 'menunggu';
        $antrean->antrean_waktu_checkin = Carbon::now();
        $antrean->save();

        return response()->json([
            'status' => 'success',
            'message' => "Check-in berhasil untuk antrean {$antrean->antrean_nomor}. Pasien telah masuk dalam daftar tunggu pemeriksaan.",
            'data' => [
                'antrean_id' => $antrean->antrean_id,
                'antrean_nomor' => $antrean->antrean_nomor,
                'antrean_angka' => $antrean->antrean_angka,
                'antrean_status' => $antrean->antrean_status,
                'waktu_checkin' => $antrean->antrean_waktu_checkin->format('H:i:s'),
                'pasien' => [
                    'nama' => $antrean->pasien->user->user_nama ?? 'Pasien',
                    'norm' => $antrean->pasien->pasien_norm ?? null,
                    'nik' => $antrean->pasien->user->user_nik ?? null,
                ],
                'poli' => [
                    'poli_id' => $antrean->poli->poli_id,
                    'poli_nama' => $antrean->poli->poli_nama,
                ],
                'dokter' => [
                    'nama' => $antrean->dokter->user->user_nama ?? null,
                ],
            ],
        ], 200);
    }

    /**
     * Pendaftaran Walk-in di Loket (US-08).
     * Mendaftarkan pasien langsung dan mencetak tiket antrean fisik hari ini.
     */
    public function pendaftaranWalkin(Request $request): JsonResponse
    {
        $today = Carbon::today()->toDateString();

        $rules = [
            'dokter_user_id' => 'required|string|exists:dokter,dokter_user_id',
            'keluhan' => 'nullable|string|max:500',
        ];

        // Jika sudah ada pasien_user_id (pasien lama)
        if ($request->filled('pasien_user_id')) {
            $rules['pasien_user_id'] = 'required|string|exists:pasien,pasien_user_id';
        } else {
            // Pasien baru mendaftar di loket
            $rules['nik'] = 'required|digits:16|unique:user,user_nik';
            $rules['nama'] = 'required|string|max:100';
            $rules['nowa'] = 'required|string|min:9|max:15';
            $rules['email'] = 'nullable|email|max:100';
            $rules['jeniskelamin'] = 'nullable|in:Laki-laki,Perempuan';
            $rules['tgllahir'] = 'nullable|date_format:Y-m-d';
            $rules['alamat'] = 'nullable|string|max:255';
            $rules['nobpjs'] = 'nullable|string|max:30';
            $rules['goldarah'] = 'nullable|in:A,B,AB,O';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $antrean = DB::transaction(function () use ($request, $today) {
                // 1. Cek kuota dokter hari ini
                $quotaStatus = $this->quotaEngine->getDoctorQuotaStatus($request->dokter_user_id, $today);
                if (!$quotaStatus['available']) {
                    throw new \DomainException($quotaStatus['message']);
                }

                $dokter = Dokter::with('poli')->findOrFail($request->dokter_user_id);
                $jadwal = $quotaStatus['jadwal'];

                // 2. Resolve Pasien ID
                $pasienUserId = null;
                if ($request->filled('pasien_user_id')) {
                    $pasienUserId = $request->pasien_user_id;
                } else {
                    // Create new user & pasien record
                    $latestUser = User::where('user_id', 'LIKE', 'USR-PAS-%')
                        ->orderByRaw('CAST(SUBSTRING(user_id, 9) AS UNSIGNED) DESC')
                        ->lockForUpdate()
                        ->first();
                    $nextUserSeq = $latestUser ? (((int) substr($latestUser->user_id, 8)) + 1) : 1;
                    $pasienUserId = sprintf('USR-PAS-%04d', $nextUserSeq);

                    $currentYear = date('Y');
                    $latestPasien = Pasien::where('pasien_norm', 'LIKE', "RM-{$currentYear}-%")
                        ->orderByRaw('CAST(SUBSTRING(pasien_norm, 9) AS UNSIGNED) DESC')
                        ->lockForUpdate()
                        ->first();
                    $nextRmSeq = $latestPasien ? (((int) substr($latestPasien->pasien_norm, 8)) + 1) : 1;
                    $norm = sprintf('RM-%s-%04d', $currentYear, $nextRmSeq);

                    // Parse NIK if gender / birthdate missing
                    $nikDetails = RegisterController::parseNikDetails($request->nik);
                    $gender = $request->jeniskelamin ?? ($nikDetails['gender'] ?? 'Laki-laki');
                    $birthDate = $request->tgllahir ?? ($nikDetails['birth_date'] ?? date('Y-m-d'));

                    $user = User::create([
                        'user_id' => $pasienUserId,
                        'user_role' => 'pasien',
                        'user_nama' => $request->nama,
                        'user_nik' => $request->nik,
                        'user_nowa' => $request->nowa,
                        'user_email' => $request->email,
                        'user_password' => Hash::make('Pasien' . date('Y') . '!'),
                        'user_status' => 'aktif',
                    ]);

                    Pasien::create([
                        'pasien_user_id' => $pasienUserId,
                        'pasien_role' => 'pasien',
                        'pasien_norm' => $norm,
                        'pasien_jeniskelamin' => $gender,
                        'pasien_tgllahir' => $birthDate,
                        'pasien_alamat' => $request->alamat ?? '-',
                        'pasien_nobpjs' => $request->nobpjs,
                        'pasien_goldarah' => $request->goldarah,
                        'pasien_poli_id' => $dokter->dokter_poli_id,
                    ]);
                }

                // 3. Allocate sequential queue number
                $queueData = $this->quotaEngine->allocateQueueNumber($dokter->dokter_poli_id, $today);
                $qrCode = $this->quotaEngine->generateQrCodeToken();

                return Antrean::create([
                    'antrean_nomor' => $queueData['antrean_nomor'],
                    'antrean_angka' => $queueData['antrean_angka'],
                    'antrean_pasien_user_id' => $pasienUserId,
                    'antrean_dokter_user_id' => $request->dokter_user_id,
                    'antrean_poli_id' => $dokter->dokter_poli_id,
                    'antrean_jadwal_id' => $jadwal->jadwal_id,
                    'antrean_tanggal' => $today,
                    'antrean_jenis' => 'walkin',
                    'antrean_status' => 'menunggu', // Walk-in is immediately checked-in
                    'antrean_qr_code' => $qrCode,
                    'antrean_waktu_checkin' => Carbon::now(),
                    'antrean_catatan' => $request->keluhan,
                ]);
            });

            $antrean->load(['pasien.user', 'poli', 'dokter.user', 'jadwal']);

            return response()->json([
                'status' => 'success',
                'message' => "Pendaftaran walk-in berhasil. Nomor antrean {$antrean->antrean_nomor} siap dicetak pada printer termal.",
                'data' => [
                    'antrean_id' => $antrean->antrean_id,
                    'antrean_nomor' => $antrean->antrean_nomor,
                    'antrean_angka' => $antrean->antrean_angka,
                    'antrean_tanggal' => $antrean->antrean_tanggal->format('Y-m-d'),
                    'antrean_status' => $antrean->antrean_status,
                    'antrean_jenis' => $antrean->antrean_jenis,
                    'antrean_qr_code' => $antrean->antrean_qr_code,
                    'waktu_cetak' => Carbon::now()->format('Y-m-d H:i:s'),
                    'pasien' => [
                        'user_id' => $antrean->pasien->pasien_user_id,
                        'nama' => $antrean->pasien->user->user_nama ?? null,
                        'norm' => $antrean->pasien->pasien_norm ?? null,
                    ],
                    'poli' => [
                        'poli_id' => $antrean->poli->poli_id,
                        'poli_nama' => $antrean->poli->poli_nama,
                    ],
                    'dokter' => [
                        'nama' => $antrean->dokter->user->user_nama ?? null,
                    ],
                ],
            ], 201);
        } catch (\DomainException $de) {
            return response()->json([
                'status' => 'error',
                'message' => $de->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error("Error pendaftaran walk-in: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat mendaftarkan pasien walk-in: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memanggil antrean pasien (Multi-Channel Call: TV TTS + SMTP Email) (US-04).
     */
    public function panggil(string $id, Request $request): JsonResponse
    {
        $antrean = Antrean::with(['pasien.user', 'poli', 'dokter.user'])->find($id);

        if (!$antrean) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data antrean tidak ditemukan.',
            ], 404);
        }

        if (in_array($antrean->antrean_status, ['selesai', 'batal'])) {
            return response()->json([
                'status' => 'error',
                'message' => "Antrean sudah berstatus {$antrean->antrean_status} dan tidak dapat dipanggil.",
            ], 422);
        }

        $antrean->antrean_status = 'dipanggil';
        $antrean->antrean_waktu_panggil = Carbon::now();
        $antrean->antrean_panggilan_ke = ($antrean->antrean_panggilan_ke ?? 0) + 1;
        $antrean->save();

        // Notifikasi Multi-Channel: Kirim Email Panggilan via SMTP (Menggantikan WhatsApp)
        $emailRecipient = $antrean->pasien->user->user_email ?? null;
        if (!empty($emailRecipient)) {
            try {
                Mail::to($emailRecipient)->send(new QueueCalledMail($antrean));
            } catch (\Throwable $mailErr) {
                Log::error("Gagal mengirim email panggilan antrean #{$antrean->antrean_id}: " . $mailErr->getMessage());
            }
        }

        $ttsText = sprintf(
            'Nomor antrean %s, silakan menuju %s.',
            $antrean->antrean_nomor,
            $antrean->poli->poli_nama ?? 'Poliklinik'
        );

        return response()->json([
            'status' => 'success',
            'message' => "Antrean {$antrean->antrean_nomor} berhasil dipanggil (panggilan ke-{$antrean->antrean_panggilan_ke}).",
            'data' => [
                'antrean_id' => $antrean->antrean_id,
                'antrean_nomor' => $antrean->antrean_nomor,
                'antrean_status' => $antrean->antrean_status,
                'antrean_panggilan_ke' => $antrean->antrean_panggilan_ke,
                'waktu_panggil' => $antrean->antrean_waktu_panggil->format('H:i:s'),
                'teks_tts' => $ttsText,
                'email_terkirim' => !empty($emailRecipient),
            ],
        ], 200);
    }

    /**
     * Memanggil ulang antrean pasien (Recall) (US-04).
     */
    public function panggilUlang(string $id, Request $request): JsonResponse
    {
        return $this->panggil($id, $request);
    }

    /**
     * Melewati nomor antrean karena pasien tidak hadir (Skip) (US-04).
     */
    public function lewati(string $id, Request $request): JsonResponse
    {
        $antrean = Antrean::find($id);

        if (!$antrean) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data antrean tidak ditemukan.',
            ], 404);
        }

        $antrean->antrean_status = 'dilewati';
        $antrean->save();

        return response()->json([
            'status' => 'success',
            'message' => "Antrean {$antrean->antrean_nomor} telah dilewati.",
            'data' => [
                'antrean_id' => $antrean->antrean_id,
                'antrean_nomor' => $antrean->antrean_nomor,
                'antrean_status' => $antrean->antrean_status,
            ],
        ], 200);
    }

    /**
     * Memperbarui status antrean menjadi 'sedang_diperiksa'.
     */
    public function layani(string $id, Request $request): JsonResponse
    {
        $antrean = Antrean::find($id);

        if (!$antrean) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data antrean tidak ditemukan.',
            ], 404);
        }

        $antrean->antrean_status = 'sedang_diperiksa';
        $antrean->save();

        return response()->json([
            'status' => 'success',
            'message' => "Antrean {$antrean->antrean_nomor} sedang dalam pemeriksaan dokter.",
            'data' => [
                'antrean_id' => $antrean->antrean_id,
                'antrean_nomor' => $antrean->antrean_nomor,
                'antrean_status' => $antrean->antrean_status,
            ],
        ], 200);
    }

    /**
     * Mengambil daftar antrean hari ini untuk dikelola petugas loket.
     */
    public function antreanHariIni(Request $request): JsonResponse
    {
        $today = Carbon::today()->toDateString();

        $query = Antrean::with(['pasien.user', 'poli', 'dokter.user', 'jadwal'])
            ->where('antrean_tanggal', $today);

        if ($request->filled('poli_id')) {
            $query->where('antrean_poli_id', $request->poli_id);
        }

        if ($request->filled('status')) {
            $query->where('antrean_status', $request->status);
        }

        $antreans = $query->orderBy('antrean_angka', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'tanggal' => $today,
                'total' => $antreans->count(),
                'antrean' => $antreans->map(function ($a) {
                    return [
                        'antrean_id' => $a->antrean_id,
                        'antrean_nomor' => $a->antrean_nomor,
                        'antrean_angka' => $a->antrean_angka,
                        'antrean_status' => $a->antrean_status,
                        'antrean_jenis' => $a->antrean_jenis,
                        'antrean_panggilan_ke' => $a->antrean_panggilan_ke,
                        'antrean_qr_code' => $a->antrean_qr_code,
                        'waktu_checkin' => $a->antrean_waktu_checkin?->format('H:i:s'),
                        'waktu_panggil' => $a->antrean_waktu_panggil?->format('H:i:s'),
                        'pasien' => [
                            'user_id' => $a->pasien->pasien_user_id ?? null,
                            'nama' => $a->pasien->user->user_nama ?? 'Pasien Walk-in',
                            'norm' => $a->pasien->pasien_norm ?? null,
                            'nik' => $a->pasien->user->user_nik ?? null,
                        ],
                        'poli' => [
                            'poli_id' => $a->poli->poli_id,
                            'poli_nama' => $a->poli->poli_nama,
                        ],
                        'dokter' => [
                            'nama' => $a->dokter->user->user_nama ?? null,
                        ],
                    ];
                }),
            ],
        ], 200);
    }
}
