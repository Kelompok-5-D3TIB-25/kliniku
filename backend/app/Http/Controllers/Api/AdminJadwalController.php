<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Jadwal;
use App\Services\QuotaEngineService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminJadwalController extends Controller
{
    protected QuotaEngineService $quotaEngine;

    public function __construct(QuotaEngineService $quotaEngine)
    {
        $this->quotaEngine = $quotaEngine;
    }

    /**
     * Menampilkan daftar jadwal praktik dan kuota dokter (US-06).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Jadwal::with(['dokter.user', 'dokter.poli']);

        if ($request->filled('dokter_user_id')) {
            $query->where('jadwal_dokter_user_id', $request->dokter_user_id);
        }

        if ($request->filled('poli_id')) {
            $query->whereHas('dokter', function ($q) use ($request) {
                $q->where('dokter_poli_id', $request->poli_id);
            });
        }

        if ($request->filled('hari')) {
            $query->where('jadwal_hari', $request->hari);
        }

        if ($request->filled('status')) {
            $query->where('jadwal_status', $request->status);
        }

        $jadwals = $query->orderBy('jadwal_hari', 'asc')
            ->orderBy('jadwal_jammulai', 'asc')
            ->get();

        $data = $jadwals->map(function ($j) {
            return [
                'jadwal_id' => $j->jadwal_id,
                'dokter' => [
                    'dokter_user_id' => $j->jadwal_dokter_user_id,
                    'nama' => $j->dokter->user->user_nama ?? null,
                    'spesialisasi' => $j->dokter->dokter_spesialisasi ?? null,
                    'poli' => $j->dokter->poli->poli_nama ?? null,
                ],
                'hari_angka' => $j->jadwal_hari,
                'hari_nama' => $this->quotaEngine->getDayName($j->jadwal_hari),
                'jam_mulai' => $j->jadwal_jammulai,
                'jam_selesai' => $j->jadwal_jamselesai,
                'kuota' => $j->jadwal_kuota,
                'status' => $j->jadwal_status,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $jadwals->count(),
                'jadwal' => $data,
            ],
        ], 200);
    }

    /**
     * Membuat jadwal praktik baru untuk dokter (US-06).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'dokter_user_id' => 'required|string|exists:dokter,dokter_user_id',
            'hari' => 'required|integer|between:1,7',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'kuota' => 'nullable|integer|min:1|max:200',
            'status' => 'nullable|in:aktif,cuti,tutup',
        ], [
            'dokter_user_id.required' => 'Dokter wajib dipilih.',
            'dokter_user_id.exists' => 'Dokter tidak ditemukan.',
            'hari.required' => 'Hari praktik wajib diisi (1=Senin s/d 7=Minggu).',
            'jam_mulai.required' => 'Jam mulai wajib diisi (HH:MM).',
            'jam_selesai.required' => 'Jam selesai wajib diisi (HH:MM).',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Cek bentrok jadwal dokter pada hari yang sama
        $conflict = Jadwal::where('jadwal_dokter_user_id', $request->dokter_user_id)
            ->where('jadwal_hari', $request->hari)
            ->first();

        if ($conflict) {
            return response()->json([
                'status' => 'error',
                'message' => "Dokter sudah memiliki jadwal pada hari " . $this->quotaEngine->getDayName($request->hari) . " (ID Jadwal: {$conflict->jadwal_id}). Silakan perbarui jadwal yang sudah ada.",
            ], 422);
        }

        $jadwal = Jadwal::create([
            'jadwal_dokter_user_id' => $request->dokter_user_id,
            'jadwal_hari' => $request->hari,
            'jadwal_jammulai' => $request->jam_mulai,
            'jadwal_jamselesai' => $request->jam_selesai,
            'jadwal_kuota' => $request->kuota ?? 25,
            'jadwal_status' => $request->status ?? 'aktif',
        ]);

        $jadwal->load(['dokter.user', 'dokter.poli']);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal praktik dokter berhasil ditambahkan.',
            'data' => [
                'jadwal_id' => $jadwal->jadwal_id,
                'dokter_nama' => $jadwal->dokter->user->user_nama ?? null,
                'hari' => $this->quotaEngine->getDayName($jadwal->jadwal_hari),
                'jam_mulai' => $jadwal->jadwal_jammulai,
                'jam_selesai' => $jadwal->jadwal_jamselesai,
                'kuota' => $jadwal->jadwal_kuota,
                'status' => $jadwal->jadwal_status,
            ],
        ], 201);
    }

    /**
     * Memperbarui jadwal praktik dokter atau mengubah kuota / status cuti (US-06).
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $jadwal = Jadwal::with(['dokter.user'])->find($id);

        if (!$jadwal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal praktik tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'hari' => 'nullable|integer|between:1,7',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i',
            'kuota' => 'nullable|integer|min:1|max:200',
            'status' => 'nullable|in:aktif,cuti,tutup',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->filled('hari')) {
            $jadwal->jadwal_hari = $request->hari;
        }
        if ($request->filled('jam_mulai')) {
            $jadwal->jadwal_jammulai = $request->jam_mulai;
        }
        if ($request->filled('jam_selesai')) {
            $jadwal->jadwal_jamselesai = $request->jam_selesai;
        }
        if ($request->filled('kuota')) {
            $jadwal->jadwal_kuota = $request->kuota;
        }
        if ($request->filled('status')) {
            $jadwal->jadwal_status = $request->status;
        }

        $jadwal->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal praktik dan kuota dokter berhasil diperbarui.',
            'data' => [
                'jadwal_id' => $jadwal->jadwal_id,
                'dokter_nama' => $jadwal->dokter->user->user_nama ?? null,
                'hari' => $this->quotaEngine->getDayName($jadwal->jadwal_hari),
                'jam_mulai' => $jadwal->jadwal_jammulai,
                'jam_selesai' => $jadwal->jadwal_jamselesai,
                'kuota' => $jadwal->jadwal_kuota,
                'status' => $jadwal->jadwal_status,
            ],
        ], 200);
    }

    /**
     * Menghapus jadwal praktik dokter.
     */
    public function destroy(string $id): JsonResponse
    {
        $jadwal = Jadwal::find($id);

        if (!$jadwal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal tidak ditemukan.',
            ], 404);
        }

        // Cek jika terdapat antrean aktif terkait jadwal ini
        $activeBookings = Antrean::where('antrean_jadwal_id', $jadwal->jadwal_id)
            ->whereIn('antrean_status', ['menunggu_checkin', 'menunggu', 'dipanggil', 'sedang_diperiksa'])
            ->where('antrean_tanggal', '>=', Carbon::today()->toDateString())
            ->count();

        if ($activeBookings > 0) {
            return response()->json([
                'status' => 'error',
                'message' => "Tidak dapat menghapus jadwal. Terdapat {$activeBookings} antrean aktif yang masih terdaftar pada jadwal ini. Anda dapat mengubah status jadwal menjadi 'tutup' atau 'cuti'.",
            ], 422);
        }

        $jadwal->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal praktik berhasil dihapus.',
        ], 200);
    }
}
