<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmedMail;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Services\QuotaEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    protected QuotaEngineService $quotaEngine;

    public function __construct(QuotaEngineService $quotaEngine)
    {
        $this->quotaEngine = $quotaEngine;
    }

    /**
     * Mengambil daftar dokter beserta jadwal praktiknya (dapat difilter berdasarkan poli_id).
     */
    public function getDokterList(Request $request): JsonResponse
    {
        $query = Dokter::with(['user:user_id,user_nama,user_status', 'poli:poli_id,poli_nama', 'jadwals']);

        if ($request->filled('poli_id')) {
            $query->where('dokter_poli_id', $request->poli_id);
        }

        $dokters = $query->get()->map(function ($dokter) {
            return [
                'dokter_user_id' => $dokter->dokter_user_id,
                'nama' => $dokter->user->user_nama ?? null,
                'spesialisasi' => $dokter->dokter_spesialisasi,
                'sip' => $dokter->dokter_sip,
                'kuota_harian' => $dokter->dokter_kuotaharian,
                'poli' => [
                    'poli_id' => $dokter->poli->poli_id ?? null,
                    'poli_nama' => $dokter->poli->poli_nama ?? null,
                ],
                'jadwal' => $dokter->jadwals->map(function ($jadwal) {
                    return [
                        'jadwal_id' => $jadwal->jadwal_id,
                        'hari_angka' => $jadwal->jadwal_hari,
                        'hari_nama' => $this->quotaEngine->getDayName($jadwal->jadwal_hari),
                        'jam_mulai' => $jadwal->jadwal_jammulai,
                        'jam_selesai' => $jadwal->jadwal_jamselesai,
                        'kuota' => $jadwal->jadwal_kuota,
                        'status' => $jadwal->jadwal_status,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $dokters,
        ], 200);
    }

    /**
     * Cek ketersediaan kuota dokter pada tanggal tertentu (H-7 s/d Hari H).
     */
    public function cekKuota(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'dokter_user_id' => 'required|string|exists:dokter,dokter_user_id',
            'tanggal' => 'required|date_format:Y-m-d',
        ], [
            'dokter_user_id.required' => 'ID Dokter wajib diisi.',
            'dokter_user_id.exists' => 'Dokter tidak ditemukan.',
            'tanggal.required' => 'Tanggal pemeriksaan wajib diisi.',
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validasi window H-7 s/d Hari H
        $windowCheck = $this->quotaEngine->isDateAllowed($request->tanggal);
        if (!$windowCheck['allowed']) {
            return response()->json([
                'status' => 'error',
                'message' => $windowCheck['message'],
            ], 422);
        }

        // Cek kuota
        $quotaStatus = $this->quotaEngine->getDoctorQuotaStatus($request->dokter_user_id, $request->tanggal);

        return response()->json([
            'status' => 'success',
            'data' => [
                'tanggal' => $request->tanggal,
                'available' => $quotaStatus['available'],
                'max_quota' => $quotaStatus['max_quota'] ?? 0,
                'booked' => $quotaStatus['booked'] ?? 0,
                'remaining' => $quotaStatus['remaining'] ?? 0,
                'message' => $quotaStatus['message'],
                'dokter' => isset($quotaStatus['dokter']) ? [
                    'dokter_user_id' => $quotaStatus['dokter']->dokter_user_id,
                    'nama' => $quotaStatus['dokter']->user->user_nama ?? null,
                    'spesialisasi' => $quotaStatus['dokter']->dokter_spesialisasi,
                    'poli' => $quotaStatus['dokter']->poli->poli_nama ?? null,
                ] : null,
                'jadwal' => isset($quotaStatus['jadwal']) ? [
                    'jadwal_id' => $quotaStatus['jadwal']->jadwal_id,
                    'jam_mulai' => $quotaStatus['jadwal']->jadwal_jammulai,
                    'jam_selesai' => $quotaStatus['jadwal']->jadwal_jamselesai,
                    'status' => $quotaStatus['jadwal']->jadwal_status,
                ] : null,
            ],
        ], 200);
    }

    /**
     * Membuat pemesanan antrean online baru (US-03).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || $user->user_role !== 'pasien') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Fitur pemesanan online khusus untuk akun pasien.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'dokter_user_id' => 'required|string|exists:dokter,dokter_user_id',
            'tanggal' => 'required|date_format:Y-m-d',
            'keluhan' => 'nullable|string|max:500',
        ], [
            'dokter_user_id.required' => 'Dokter wajib dipilih.',
            'dokter_user_id.exists' => 'Dokter yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal periksa wajib diisi.',
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tanggal = $request->tanggal;
        $dokterUserId = $request->dokter_user_id;

        // 1. Validasi rentang tanggal H-7 s/d Hari H
        $windowCheck = $this->quotaEngine->isDateAllowed($tanggal);
        if (!$windowCheck['allowed']) {
            return response()->json([
                'status' => 'error',
                'message' => $windowCheck['message'],
            ], 422);
        }

        // 2. Cegah duplikasi antrean aktif untuk pasien pada tanggal yang sama
        $existing = Antrean::where('antrean_pasien_user_id', $user->user_id)
            ->where('antrean_tanggal', $tanggal)
            ->whereIn('antrean_status', ['menunggu_checkin', 'menunggu', 'dipanggil', 'sedang_diperiksa'])
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => "Anda sudah memiliki antrean aktif ({$existing->antrean_nomor}) untuk tanggal {$tanggal}.",
                'data' => [
                    'antrean_id' => $existing->antrean_id,
                    'antrean_nomor' => $existing->antrean_nomor,
                ],
            ], 422);
        }

        // 3. Eksekusi Quota Engine & Transaction
        try {
            $antrean = DB::transaction(function () use ($user, $dokterUserId, $tanggal, $request) {
                // Check quota under lock
                $quotaStatus = $this->quotaEngine->getDoctorQuotaStatus($dokterUserId, $tanggal);

                if (!$quotaStatus['available']) {
                    throw new \DomainException($quotaStatus['message']);
                }

                $dokter = Dokter::with('poli')->findOrFail($dokterUserId);
                $jadwal = $quotaStatus['jadwal'];

                // Allocate sequential queue number
                $queueData = $this->quotaEngine->allocateQueueNumber($dokter->dokter_poli_id, $tanggal);
                $qrCode = $this->quotaEngine->generateQrCodeToken();

                $newAntrean = Antrean::create([
                    'antrean_nomor' => $queueData['antrean_nomor'],
                    'antrean_angka' => $queueData['antrean_angka'],
                    'antrean_pasien_user_id' => $user->user_id,
                    'antrean_dokter_user_id' => $dokterUserId,
                    'antrean_poli_id' => $dokter->dokter_poli_id,
                    'antrean_jadwal_id' => $jadwal->jadwal_id,
                    'antrean_tanggal' => $tanggal,
                    'antrean_jenis' => 'online',
                    'antrean_status' => 'menunggu_checkin',
                    'antrean_qr_code' => $qrCode,
                    'antrean_catatan' => $request->keluhan,
                ]);

                return $newAntrean;
            });

            $antrean->load(['dokter.user', 'poli', 'pasien.user', 'jadwal']);

            // 4. Kirim Konfirmasi Email dengan E-Tiket & QR Code (SMTP Email)
            if (!empty($user->user_email)) {
                try {
                    Mail::to($user->user_email)->send(new BookingConfirmedMail($antrean));
                } catch (\Throwable $mailError) {
                    Log::error("Gagal mengirim email konfirmasi booking antrean #{$antrean->antrean_id}: " . $mailError->getMessage());
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pemesanan antrean berhasil! E-tiket telah dibuat dan dikirim ke email Anda.',
                'data' => [
                    'antrean_id' => $antrean->antrean_id,
                    'antrean_nomor' => $antrean->antrean_nomor,
                    'antrean_angka' => $antrean->antrean_angka,
                    'antrean_tanggal' => $antrean->antrean_tanggal->format('Y-m-d'),
                    'antrean_status' => $antrean->antrean_status,
                    'antrean_qr_code' => $antrean->antrean_qr_code,
                    'poli' => [
                        'poli_id' => $antrean->poli->poli_id,
                        'poli_nama' => $antrean->poli->poli_nama,
                    ],
                    'dokter' => [
                        'dokter_user_id' => $antrean->dokter->dokter_user_id,
                        'nama' => $antrean->dokter->user->user_nama ?? null,
                        'spesialisasi' => $antrean->dokter->dokter_spesialisasi,
                    ],
                    'jadwal' => [
                        'jam_mulai' => $antrean->jadwal->jadwal_jammulai ?? null,
                        'jam_selesai' => $antrean->jadwal->jadwal_jamselesai ?? null,
                    ],
                    'catatan' => $antrean->antrean_catatan,
                ],
            ], 201);
        } catch (\DomainException $domainException) {
            return response()->json([
                'status' => 'error',
                'message' => $domainException->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error("Error saat booking antrean: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat memproses pemesanan antrean: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil detail E-Tiket antrean berdasarkan ID.
     */
    public function getTiket(string $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $antrean = Antrean::with(['dokter.user', 'poli', 'pasien.user', 'jadwal'])->find($id);

        if (!$antrean) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data antrean / tiket tidak ditemukan.',
            ], 404);
        }

        // Pasien hanya boleh melihat tiket miliknya sendiri
        if ($user->user_role === 'pasien' && $antrean->antrean_pasien_user_id !== $user->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki otorisasi untuk mengakses e-tiket ini.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'antrean_id' => $antrean->antrean_id,
                'antrean_nomor' => $antrean->antrean_nomor,
                'antrean_angka' => $antrean->antrean_angka,
                'antrean_tanggal' => $antrean->antrean_tanggal->format('Y-m-d'),
                'antrean_status' => $antrean->antrean_status,
                'antrean_qr_code' => $antrean->antrean_qr_code,
                'antrean_jenis' => $antrean->antrean_jenis,
                'waktu_checkin' => $antrean->antrean_waktu_checkin?->format('H:i:s'),
                'waktu_panggil' => $antrean->antrean_waktu_panggil?->format('H:i:s'),
                'waktu_selesai' => $antrean->antrean_waktu_selesai?->format('H:i:s'),
                'poli' => [
                    'poli_id' => $antrean->poli->poli_id,
                    'poli_nama' => $antrean->poli->poli_nama,
                ],
                'dokter' => [
                    'dokter_user_id' => $antrean->dokter->dokter_user_id,
                    'nama' => $antrean->dokter->user->user_nama ?? null,
                    'spesialisasi' => $antrean->dokter->dokter_spesialisasi,
                ],
                'pasien' => [
                    'user_id' => $antrean->pasien->pasien_user_id,
                    'nama' => $antrean->pasien->user->user_nama ?? null,
                    'nik' => $antrean->pasien->user->user_nik ?? null,
                    'norm' => $antrean->pasien->pasien_norm,
                ],
                'jadwal' => [
                    'jam_mulai' => $antrean->jadwal->jadwal_jammulai ?? null,
                    'jam_selesai' => $antrean->jadwal->jadwal_jamselesai ?? null,
                ],
                'catatan' => $antrean->antrean_catatan,
            ],
        ], 200);
    }

    /**
     * Membatalkan pemesanan antrean sebelum check-in.
     */
    public function cancel(string $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $antrean = Antrean::find($id);

        if (!$antrean) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data antrean tidak ditemukan.',
            ], 404);
        }

        if ($user->user_role === 'pasien' && $antrean->antrean_pasien_user_id !== $user->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak untuk membatalkan antrean ini.',
            ], 403);
        }

        if ($antrean->antrean_status !== 'menunggu_checkin') {
            return response()->json([
                'status' => 'error',
                'message' => "Antrean dengan status '{$antrean->antrean_status}' tidak dapat dibatalkan.",
            ], 422);
        }

        $antrean->antrean_status = 'batal';
        $antrean->save();

        return response()->json([
            'status' => 'success',
            'message' => "Pemesanan antrean {$antrean->antrean_nomor} berhasil dibatalkan.",
            'data' => [
                'antrean_id' => $antrean->antrean_id,
                'antrean_status' => $antrean->antrean_status,
            ],
        ], 200);
    }
}
