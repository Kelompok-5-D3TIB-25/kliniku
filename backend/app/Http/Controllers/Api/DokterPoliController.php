<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\RekamMedis;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DokterPoliController extends Controller
{
    /**
     * Mengambil daftar antrean pasien hari ini untuk dokter yang sedang login (US-05).
     */
    public function antreanHariIni(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $dokter = Dokter::with('poli')->find($user->user_id);

        if (!$dokter) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil dokter tidak ditemukan untuk pengguna ini.',
            ], 404);
        }

        $query = Antrean::with(['pasien.user'])
            ->where('antrean_dokter_user_id', $dokter->dokter_user_id)
            ->where('antrean_tanggal', $today);

        if ($request->filled('status')) {
            $query->where('antrean_status', $request->status);
        }

        $antreans = $query->orderBy('antrean_angka', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'dokter' => [
                    'dokter_user_id' => $dokter->dokter_user_id,
                    'nama' => $user->user_nama,
                    'spesialisasi' => $dokter->dokter_spesialisasi,
                    'poli' => $dokter->poli->poli_nama ?? null,
                ],
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
                        'catatan_keluhan_awal' => $a->antrean_catatan,
                        'waktu_checkin' => $a->antrean_waktu_checkin?->format('H:i:s'),
                        'waktu_panggil' => $a->antrean_waktu_panggil?->format('H:i:s'),
                        'waktu_selesai' => $a->antrean_waktu_selesai?->format('H:i:s'),
                        'pasien' => [
                            'user_id' => $a->antrean_pasien_user_id,
                            'nama' => $a->pasien->user->user_nama ?? 'Pasien Walk-in',
                            'norm' => $a->pasien->pasien_norm ?? null,
                            'nik' => $a->pasien->user->user_nik ?? null,
                            'jenis_kelamin' => $a->pasien->pasien_jeniskelamin ?? null,
                            'tgl_lahir' => $a->pasien->pasien_tgllahir ?? null,
                            'gol_darah' => $a->pasien->pasien_goldarah ?? null,
                            'alergi_obat' => $a->pasien->pasien_alergiobat ?? null,
                        ],
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Melihat riwayat rekam medis pasien sebelumnya (US-05).
     * Nilai keluhan, diagnosa, vital signs, dsb didekripsi otomatis dari enkripsi AES-256.
     */
    public function riwayatPasien(string $pasienUserId, Request $request): JsonResponse
    {
        $pasien = Pasien::with('user')->find($pasienUserId);

        if (!$pasien) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pasien tidak ditemukan.',
            ], 404);
        }

        $records = RekamMedis::with(['dokter.user', 'poli'])
            ->where('rekam_medis_pasien_user_id', $pasienUserId)
            ->orderBy('rekam_medis_tanggal', 'desc')
            ->orderBy('rekam_medis_id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'pasien' => [
                    'user_id' => $pasien->pasien_user_id,
                    'nama' => $pasien->user->user_nama ?? null,
                    'norm' => $pasien->pasien_norm,
                    'nik' => $pasien->user->user_nik ?? null,
                    'jenis_kelamin' => $pasien->pasien_jeniskelamin,
                    'tgl_lahir' => $pasien->pasien_tgllahir,
                    'gol_darah' => $pasien->pasien_goldarah,
                    'alergi_obat' => $pasien->pasien_alergiobat,
                ],
                'total_riwayat' => $records->count(),
                'riwayat' => $records->map(function ($r) {
                    return [
                        'rekam_medis_id' => $r->rekam_medis_id,
                        'tanggal' => $r->rekam_medis_tanggal->format('Y-m-d'),
                        'dokter_nama' => $r->dokter->user->user_nama ?? null,
                        'poli_nama' => $r->poli->poli_nama ?? null,
                        'keluhan' => $r->rekam_medis_keluhan,
                        'diagnosa' => $r->rekam_medis_diagnosa,
                        'tanda_vital' => $r->rekam_medis_tanda_vital,
                        'tindakan' => $r->rekam_medis_tindakan,
                        'resep_obat' => $r->rekam_medis_resep_obat,
                        'waktu_pencatatan' => $r->created_at?->format('Y-m-d H:i:s'),
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Menyimpan hasil pemeriksaan dokter (Rekam Medis) dan menyelesaikan antrean (US-05).
     * Enkripsi AES-256 diterapkan otomatis ke keluhan, diagnosa, tanda vital, tindakan, resep.
     */
    public function simpanPemeriksaan(Request $request): JsonResponse
    {
        $user = $request->user();
        $dokter = Dokter::findOrFail($user->user_id);

        $validator = Validator::make($request->all(), [
            'antrean_id' => 'required|integer|exists:antrean,antrean_id',
            'keluhan' => 'required|string',
            'diagnosa' => 'required|string',
            'tanda_vital' => 'nullable|string',
            'tindakan' => 'nullable|string',
            'resep_obat' => 'nullable|string',
        ], [
            'antrean_id.required' => 'ID Antrean wajib diisi.',
            'antrean_id.exists' => 'Data antrean tidak ditemukan.',
            'keluhan.required' => 'Keluhan pasien wajib diisi.',
            'diagnosa.required' => 'Diagnosa dokter (ICD-10) wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $antrean = Antrean::with('pasien')->findOrFail($request->antrean_id);

        if ($antrean->antrean_status === 'selesai') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemeriksaan untuk nomor antrean ini telah selesai dan rekam medis sudah tercatat.',
            ], 422);
        }

        try {
            $rekamMedis = DB::transaction(function () use ($antrean, $dokter, $request) {
                // Simpan Rekam Medis dengan Enkripsi Simetris AES-256
                $rm = RekamMedis::create([
                    'rekam_medis_antrean_id' => $antrean->antrean_id,
                    'rekam_medis_pasien_user_id' => $antrean->antrean_pasien_user_id,
                    'rekam_medis_dokter_user_id' => $dokter->dokter_user_id,
                    'rekam_medis_poli_id' => $dokter->dokter_poli_id,
                    'rekam_medis_tanggal' => Carbon::today()->toDateString(),
                    'rekam_medis_keluhan' => $request->keluhan,
                    'rekam_medis_diagnosa' => $request->diagnosa,
                    'rekam_medis_tanda_vital' => $request->tanda_vital ?? '-',
                    'rekam_medis_tindakan' => $request->tindakan ?? '-',
                    'rekam_medis_resep_obat' => $request->resep_obat ?? '-',
                ]);

                // Update status antrean menjadi selesai
                $antrean->antrean_status = 'selesai';
                $antrean->antrean_waktu_selesai = Carbon::now();
                $antrean->save();

                return $rm;
            });

            $rekamMedis->load(['pasien.user', 'dokter.user', 'poli']);

            return response()->json([
                'status' => 'success',
                'message' => "Pemeriksaan pasien {$rekamMedis->pasien->user->user_nama} selesai. Rekam medis berhasil dienkripsi dan disimpan.",
                'data' => [
                    'rekam_medis_id' => $rekamMedis->rekam_medis_id,
                    'antrean_nomor' => $antrean->antrean_nomor,
                    'antrean_status' => $antrean->antrean_status,
                    'tanggal' => $rekamMedis->rekam_medis_tanggal->format('Y-m-d'),
                    'pasien_nama' => $rekamMedis->pasien->user->user_nama ?? null,
                    'dokter_nama' => $rekamMedis->dokter->user->user_nama ?? null,
                    'poli_nama' => $rekamMedis->poli->poli_nama ?? null,
                    'keluhan' => $rekamMedis->rekam_medis_keluhan,
                    'diagnosa' => $rekamMedis->rekam_medis_diagnosa,
                    'tanda_vital' => $rekamMedis->rekam_medis_tanda_vital,
                    'tindakan' => $rekamMedis->rekam_medis_tindakan,
                    'resep_obat' => $rekamMedis->rekam_medis_resep_obat,
                    'waktu_selesai' => $antrean->antrean_waktu_selesai->format('H:i:s'),
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error("Error simpan rekam medis: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat menyimpan rekam medis: ' . $e->getMessage(),
            ], 500);
        }
    }
}
