<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Antrean;
use App\Models\Poli;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueTrackerController extends Controller
{
    /**
     * Jadwal Saya: Mengambil daftar antrean milik pasien yang sedang login (US-02).
     */
    public function antreanSaya(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || $user->user_role !== 'pasien') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Fitur ini hanya untuk pasien terdaftar.',
            ], 403);
        }

        $query = Antrean::with(['dokter.user', 'poli', 'jadwal'])
            ->where('antrean_pasien_user_id', $user->user_id);

        // Filter tipe: 'aktif' atau 'riwayat'
        $tipe = $request->query('tipe', 'semua');
        if ($tipe === 'aktif') {
            $query->whereIn('antrean_status', ['menunggu_checkin', 'menunggu', 'dipanggil', 'sedang_diperiksa'])
                ->where('antrean_tanggal', '>=', Carbon::today()->toDateString());
        } elseif ($tipe === 'riwayat') {
            $query->where(function ($q) {
                $q->whereIn('antrean_status', ['selesai', 'batal', 'dilewati'])
                    ->orWhere('antrean_tanggal', '<', Carbon::today()->toDateString());
            });
        }

        $antreans = $query->orderBy('antrean_tanggal', 'desc')
            ->orderBy('antrean_angka', 'asc')
            ->get();

        $data = $antreans->map(function ($antrean) {
            return [
                'antrean_id' => $antrean->antrean_id,
                'antrean_nomor' => $antrean->antrean_nomor,
                'antrean_angka' => $antrean->antrean_angka,
                'antrean_tanggal' => $antrean->antrean_tanggal->format('Y-m-d'),
                'antrean_status' => $antrean->antrean_status,
                'antrean_jenis' => $antrean->antrean_jenis,
                'antrean_qr_code' => $antrean->antrean_qr_code,
                'poli' => [
                    'poli_id' => $antrean->poli->poli_id ?? null,
                    'poli_nama' => $antrean->poli->poli_nama ?? null,
                ],
                'dokter' => [
                    'dokter_user_id' => $antrean->dokter->dokter_user_id ?? null,
                    'nama' => $antrean->dokter->user->user_nama ?? null,
                    'spesialisasi' => $antrean->dokter->dokter_spesialisasi ?? null,
                ],
                'jadwal' => [
                    'jam_mulai' => $antrean->jadwal->jadwal_jammulai ?? null,
                    'jam_selesai' => $antrean->jadwal->jadwal_jamselesai ?? null,
                ],
                'waktu_checkin' => $antrean->antrean_waktu_checkin?->format('H:i:s'),
                'waktu_panggil' => $antrean->antrean_waktu_panggil?->format('H:i:s'),
                'waktu_selesai' => $antrean->antrean_waktu_selesai?->format('H:i:s'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    /**
     * Live Queue Tracker: Memantau status real-time antrean pasien tertentu (US-02).
     */
    public function liveTracking(string $id, Request $request): JsonResponse
    {
        $antrean = Antrean::with(['dokter.user', 'poli', 'jadwal'])->find($id);

        if (!$antrean) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data antrean tidak ditemukan.',
            ], 404);
        }

        $tanggal = $antrean->antrean_tanggal->format('Y-m-d');
        $poliId = $antrean->antrean_poli_id;

        // Antrean yang sedang aktif dilayani / dipanggil di poli tersebut hari ini
        $currentlyServing = Antrean::where('antrean_poli_id', $poliId)
            ->where('antrean_tanggal', $tanggal)
            ->whereIn('antrean_status', ['sedang_diperiksa', 'dipanggil'])
            ->orderBy('antrean_waktu_panggil', 'desc')
            ->first();

        // Hitung sisa antrean yang berada di depan pasien ini (yang berstatus 'menunggu' atau 'dipanggil')
        $sisaAntreanDiDepan = 0;
        if (in_array($antrean->antrean_status, ['menunggu_checkin', 'menunggu'])) {
            $sisaAntreanDiDepan = Antrean::where('antrean_poli_id', $poliId)
                ->where('antrean_tanggal', $tanggal)
                ->where('antrean_angka', '<', $antrean->antrean_angka)
                ->whereIn('antrean_status', ['menunggu', 'dipanggil'])
                ->count();
        }

        // Total pasien dalam antrean menunggu di poli ini hari ini
        $totalMenunggu = Antrean::where('antrean_poli_id', $poliId)
            ->where('antrean_tanggal', $tanggal)
            ->where('antrean_status', 'menunggu')
            ->count();

        // Total pasien yang sudah selesai diperiksa
        $totalSelesai = Antrean::where('antrean_poli_id', $poliId)
            ->where('antrean_tanggal', $tanggal)
            ->where('antrean_status', 'selesai')
            ->count();

        // Estimasi waktu tunggu (asumsi rata-rata periksa 10 menit per pasien)
        $estimasiMenit = $sisaAntreanDiDepan * 10;

        return response()->json([
            'status' => 'success',
            'data' => [
                'tiket' => [
                    'antrean_id' => $antrean->antrean_id,
                    'antrean_nomor' => $antrean->antrean_nomor,
                    'antrean_angka' => $antrean->antrean_angka,
                    'antrean_tanggal' => $tanggal,
                    'antrean_status' => $antrean->antrean_status,
                    'antrean_jenis' => $antrean->antrean_jenis,
                    'antrean_qr_code' => $antrean->antrean_qr_code,
                    'poli' => [
                        'poli_id' => $antrean->poli->poli_id,
                        'poli_nama' => $antrean->poli->poli_nama,
                    ],
                    'dokter' => [
                        'dokter_user_id' => $antrean->dokter->dokter_user_id,
                        'nama' => $antrean->dokter->user->user_nama ?? null,
                    ],
                ],
                'live_status' => [
                    'sedang_dilayani' => $currentlyServing ? [
                        'antrean_id' => $currentlyServing->antrean_id,
                        'antrean_nomor' => $currentlyServing->antrean_nomor,
                        'antrean_angka' => $currentlyServing->antrean_angka,
                        'antrean_status' => $currentlyServing->antrean_status,
                        'waktu_panggil' => $currentlyServing->antrean_waktu_panggil?->format('H:i:s'),
                    ] : null,
                    'sisa_antrean_di_depan' => $sisaAntreanDiDepan,
                    'total_menunggu_poli' => $totalMenunggu,
                    'total_selesai_poli' => $totalSelesai,
                    'estimasi_waktu_tunggu_menit' => $estimasiMenit,
                    'pesan' => $this->generateTrackerMessage($antrean->antrean_status, $sisaAntreanDiDepan),
                ],
            ],
        ], 200);
    }

    /**
     * Mengambil daftar seluruh antrean poli untuk hari ini.
     */
    public function poliHariIni(int $poliId, Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal', Carbon::today()->toDateString());

        $poli = Poli::find($poliId);
        if (!$poli) {
            return response()->json([
                'status' => 'error',
                'message' => 'Poli tidak ditemukan.',
            ], 404);
        }

        $antreans = Antrean::with(['dokter.user', 'pasien.user'])
            ->where('antrean_poli_id', $poliId)
            ->where('antrean_tanggal', $tanggal)
            ->orderBy('antrean_angka', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'poli' => [
                    'poli_id' => $poli->poli_id,
                    'poli_nama' => $poli->poli_nama,
                ],
                'tanggal' => $tanggal,
                'total_antrean' => $antreans->count(),
                'antrean' => $antreans->map(function ($a) {
                    return [
                        'antrean_id' => $a->antrean_id,
                        'antrean_nomor' => $a->antrean_nomor,
                        'antrean_angka' => $a->antrean_angka,
                        'antrean_status' => $a->antrean_status,
                        'antrean_jenis' => $a->antrean_jenis,
                        'pasien_nama' => $a->pasien->user->user_nama ?? 'Pasien Walk-in',
                        'dokter_nama' => $a->dokter->user->user_nama ?? null,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Endpoint Monitor TV Ruang Tunggu (TTS Audio Payload & Display TV).
     */
    public function tvDisplay(Request $request): JsonResponse
    {
        $today = Carbon::today()->toDateString();
        $polis = Poli::orderBy('poli_id')->get();

        $displayData = $polis->map(function ($poli) use ($today) {
            // Antrean yang sedang dipanggil / diperiksa saat ini di poli ini
            $active = Antrean::where('antrean_poli_id', $poli->poli_id)
                ->where('antrean_tanggal', $today)
                ->whereIn('antrean_status', ['dipanggil', 'sedang_diperiksa'])
                ->orderBy('antrean_waktu_panggil', 'desc')
                ->first();

            // Total antrean hari ini
            $totalHariIni = Antrean::where('antrean_poli_id', $poli->poli_id)
                ->where('antrean_tanggal', $today)
                ->count();

            // Total menunggu
            $menungguCount = Antrean::where('antrean_poli_id', $poli->poli_id)
                ->where('antrean_tanggal', $today)
                ->where('antrean_status', 'menunggu')
                ->count();

            $ttsText = null;
            if ($active) {
                $ttsText = sprintf(
                    'Nomor antrean %s, silakan menuju ke %s.',
                    $active->antrean_nomor,
                    $poli->poli_nama
                );
            }

            return [
                'poli_id' => $poli->poli_id,
                'poli_nama' => $poli->poli_nama,
                'nomor_dipanggil' => $active?->antrean_nomor ?? '-',
                'antrean_id' => $active?->antrean_id ?? null,
                'status' => $active?->antrean_status ?? 'standby',
                'panggilan_ke' => $active?->antrean_panggilan_ke ?? 0,
                'waktu_panggil' => $active?->antrean_waktu_panggil?->format('H:i:s'),
                'teks_tts' => $ttsText,
                'sisa_antrean' => $menungguCount,
                'total_antrean' => $totalHariIni,
            ];
        });

        // Antrean terakhir yang baru saja dipanggil secara global untuk trigger sound chime / TTS
        $latestCalled = Antrean::with('poli')
            ->where('antrean_tanggal', $today)
            ->where('antrean_status', 'dipanggil')
            ->orderBy('antrean_waktu_panggil', 'desc')
            ->first();

        $globalTts = null;
        if ($latestCalled) {
            $globalTts = [
                'antrean_id' => $latestCalled->antrean_id,
                'antrean_nomor' => $latestCalled->antrean_nomor,
                'poli_nama' => $latestCalled->poli->poli_nama ?? '',
                'teks_panggilan' => sprintf(
                    'Nomor antrean %s, silakan menuju %s.',
                    $latestCalled->antrean_nomor,
                    $latestCalled->poli->poli_nama ?? 'Poliklinik'
                ),
                'waktu_panggil' => $latestCalled->antrean_waktu_panggil?->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'tanggal' => $today,
                'timestamp' => Carbon::now()->toIso8601String(),
                'display_poli' => $displayData,
                'panggilan_terbaru' => $globalTts,
            ],
        ], 200);
    }

    private function generateTrackerMessage(string $status, int $sisa): string
    {
        return match ($status) {
            'menunggu_checkin' => 'Silakan lakukan check-in di loket pendaftaran setibanya di klinik.',
            'menunggu' => $sisa === 0
                ? 'Giliran Anda sebentar lagi! Harap bersiap di dekat ruang periksa.'
                : "Masih terdapat {$sisa} pasien di depan Anda. Harap menunggu panggilan.",
            'dipanggil' => 'Nomor Anda sedang dipanggil! Segera masuki ruang periksa.',
            'sedang_diperiksa' => 'Pemeriksaan dokter sedang berlangsung.',
            'selesai' => 'Pemeriksaan telah selesai. Terima kasih telah menggunakan layanan KLINIKU.',
            'dilewati' => 'Nomor antrean Anda telah dilewati karena tidak hadir saat dipanggil. Silakan hubungi petugas loket.',
            'batal' => 'Antrean ini telah dibatalkan.',
            default => 'Status antrean tidak diketahui.',
        };
    }
}
