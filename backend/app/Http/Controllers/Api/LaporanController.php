<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Antrean;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    /**
     * Rekapitulasi Laporan Kunjungan Pasien (US-09).
     * Menyediakan agregasi metrik dan daftar riwayat kunjungan klinik.
     */
    public function laporanKunjungan(Request $request): JsonResponse
    {
        $startDate = $request->query('tanggal_mulai', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->query('tanggal_selesai', Carbon::today()->toDateString());

        $query = Antrean::with(['pasien.user', 'poli', 'dokter.user'])
            ->whereBetween('antrean_tanggal', [$startDate, $endDate]);

        if ($request->filled('poli_id')) {
            $query->where('antrean_poli_id', $request->poli_id);
        }

        if ($request->filled('dokter_user_id')) {
            $query->where('antrean_dokter_user_id', $request->dokter_user_id);
        }

        if ($request->filled('jenis')) {
            $query->where('antrean_jenis', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('antrean_status', $request->status);
        }

        $allRecords = $query->orderBy('antrean_tanggal', 'desc')
            ->orderBy('antrean_angka', 'asc')
            ->get();

        $totalKunjungan = $allRecords->count();

        // Breakdown per status
        $statusBreakdown = $allRecords->groupBy('antrean_status')->map->count();

        // Breakdown per jenis pendaftaran (online vs walkin)
        $jenisBreakdown = $allRecords->groupBy('antrean_jenis')->map->count();

        // Breakdown per poli
        $poliBreakdown = $allRecords->groupBy(fn ($item) => $item->poli->poli_nama ?? 'Lainnya')->map->count();

        // Breakdown per tanggal
        $dailyBreakdown = $allRecords->groupBy(fn ($item) => $item->antrean_tanggal->format('Y-m-d'))->map->count();

        $rows = $allRecords->map(function ($a) {
            return [
                'antrean_id' => $a->antrean_id,
                'tanggal' => $a->antrean_tanggal->format('Y-m-d'),
                'nomor_antrean' => $a->antrean_nomor,
                'angka_antrean' => $a->antrean_angka,
                'jenis' => $a->antrean_jenis,
                'status' => $a->antrean_status,
                'norm' => $a->pasien->pasien_norm ?? null,
                'nama_pasien' => $a->pasien->user->user_nama ?? 'Pasien Walk-in',
                'poli' => $a->poli->poli_nama ?? null,
                'dokter' => $a->dokter->user->user_nama ?? null,
                'waktu_checkin' => $a->antrean_waktu_checkin?->format('H:i:s'),
                'waktu_panggil' => $a->antrean_waktu_panggil?->format('H:i:s'),
                'waktu_selesai' => $a->antrean_waktu_selesai?->format('H:i:s'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'periode' => [
                    'tanggal_mulai' => $startDate,
                    'tanggal_selesai' => $endDate,
                ],
                'ringkasan' => [
                    'total_kunjungan' => $totalKunjungan,
                    'kunjungan_selesai' => $statusBreakdown['selesai'] ?? 0,
                    'kunjungan_dilewati' => $statusBreakdown['dilewati'] ?? 0,
                    'kunjungan_batal' => $statusBreakdown['batal'] ?? 0,
                    'rata_rata_per_hari' => $dailyBreakdown->count() > 0 ? round($totalKunjungan / $dailyBreakdown->count(), 1) : 0,
                ],
                'statistik' => [
                    'per_status' => $statusBreakdown,
                    'per_jenis' => $jenisBreakdown,
                    'per_poli' => $poliBreakdown,
                    'per_tanggal' => $dailyBreakdown,
                ],
                'daftar_kunjungan' => $rows,
            ],
        ], 200);
    }

    /**
     * Export Rekapitulasi Kunjungan dalam Format CSV (US-09).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->query('tanggal_mulai', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->query('tanggal_selesai', Carbon::today()->toDateString());

        $query = Antrean::with(['pasien.user', 'poli', 'dokter.user'])
            ->whereBetween('antrean_tanggal', [$startDate, $endDate]);

        if ($request->filled('poli_id')) {
            $query->where('antrean_poli_id', $request->poli_id);
        }

        if ($request->filled('dokter_user_id')) {
            $query->where('antrean_dokter_user_id', $request->dokter_user_id);
        }

        if ($request->filled('status')) {
            $query->where('antrean_status', $request->status);
        }

        $records = $query->orderBy('antrean_tanggal', 'asc')
            ->orderBy('antrean_angka', 'asc')
            ->get();

        $filename = "rekap-kunjungan-kliniku-{$startDate}-sd-{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header Row
            fputcsv($handle, [
                'ID Antrean',
                'Tanggal',
                'Nomor Antrean',
                'Jenis Pendaftaran',
                'No RM',
                'NIK',
                'Nama Pasien',
                'Poliklinik',
                'Dokter Pemeriksa',
                'Status',
                'Waktu Check-in',
                'Waktu Panggil',
                'Waktu Selesai',
                'Catatan / Keluhan',
            ]);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->antrean_id,
                    $r->antrean_tanggal->format('Y-m-d'),
                    $r->antrean_nomor,
                    strtoupper($r->antrean_jenis),
                    $r->pasien->pasien_norm ?? '-',
                    "'" . ($r->pasien->user->user_nik ?? '-'),
                    $r->pasien->user->user_nama ?? 'Pasien Walk-in',
                    $r->poli->poli_nama ?? '-',
                    $r->dokter->user->user_nama ?? '-',
                    strtoupper($r->antrean_status),
                    $r->antrean_waktu_checkin?->format('H:i:s') ?? '-',
                    $r->antrean_waktu_panggil?->format('H:i:s') ?? '-',
                    $r->antrean_waktu_selesai?->format('H:i:s') ?? '-',
                    $r->antrean_catatan ?? '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
