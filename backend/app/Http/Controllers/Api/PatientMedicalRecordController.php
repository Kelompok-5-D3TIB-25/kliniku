<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RekamMedis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientMedicalRecordController extends Controller
{
    /**
     * Melihat riwayat rekam medis pribadi pasien (US-07).
     * Enkripsi AES-256 didekripsi secara transparan dan aman.
     */
    public function riwayatMedis(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || $user->user_role !== 'pasien') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Fitur ini khusus untuk akun pasien.',
            ], 403);
        }

        $records = RekamMedis::with(['dokter.user', 'poli'])
            ->where('rekam_medis_pasien_user_id', $user->user_id)
            ->orderBy('rekam_medis_tanggal', 'desc')
            ->orderBy('rekam_medis_id', 'desc')
            ->get();

        $data = $records->map(function ($r) {
            return [
                'rekam_medis_id' => $r->rekam_medis_id,
                'tanggal' => $r->rekam_medis_tanggal->format('Y-m-d'),
                'poli' => [
                    'poli_id' => $r->poli->poli_id ?? null,
                    'poli_nama' => $r->poli->poli_nama ?? null,
                ],
                'dokter' => [
                    'dokter_user_id' => $r->dokter->dokter_user_id ?? null,
                    'nama' => $r->dokter->user->user_nama ?? null,
                    'spesialisasi' => $r->dokter->dokter_spesialisasi ?? null,
                ],
                'keluhan' => $r->rekam_medis_keluhan,
                'diagnosa' => $r->rekam_medis_diagnosa,
                'tanda_vital' => $r->rekam_medis_tanda_vital,
                'tindakan' => $r->rekam_medis_tindakan,
                'resep_obat' => $r->rekam_medis_resep_obat,
                'waktu_pemeriksaan' => $r->created_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $records->count(),
                'riwayat' => $data,
            ],
        ], 200);
    }

    /**
     * Mengambil detail satu berkas rekam medis pribadi (US-07).
     */
    public function detailMedis(string $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $record = RekamMedis::with(['dokter.user', 'poli', 'pasien.user'])->find($id);

        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Berkas rekam medis tidak ditemukan.',
            ], 404);
        }

        // Pasien hanya boleh mengakses rekam medis miliknya sendiri
        if ($user->user_role === 'pasien' && $record->rekam_medis_pasien_user_id !== $user->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak otorisasi untuk mengakses rekam medis ini.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'rekam_medis_id' => $record->rekam_medis_id,
                'tanggal' => $record->rekam_medis_tanggal->format('Y-m-d'),
                'poli' => [
                    'poli_id' => $record->poli->poli_id ?? null,
                    'poli_nama' => $record->poli->poli_nama ?? null,
                ],
                'dokter' => [
                    'dokter_user_id' => $record->dokter->dokter_user_id ?? null,
                    'nama' => $record->dokter->user->user_nama ?? null,
                    'spesialisasi' => $record->dokter->dokter_spesialisasi ?? null,
                ],
                'keluhan' => $record->rekam_medis_keluhan,
                'diagnosa' => $record->rekam_medis_diagnosa,
                'tanda_vital' => $record->rekam_medis_tanda_vital,
                'tindakan' => $record->rekam_medis_tindakan,
                'resep_obat' => $record->rekam_medis_resep_obat,
                'waktu_pemeriksaan' => $record->created_at?->format('Y-m-d H:i:s'),
            ],
        ], 200);
    }
}
