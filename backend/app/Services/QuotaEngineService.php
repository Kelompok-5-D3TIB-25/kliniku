<?php

namespace App\Services;

use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Jadwal;
use App\Models\Poli;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuotaEngineService
{
    /**
     * Maximum allowed booking window: H-7 s/d Hari H (0 to 7 days ahead).
     */
    public function isDateAllowed(string $date): array
    {
        $targetDate = Carbon::parse($date)->startOfDay();
        $today = Carbon::today();
        $maxDate = Carbon::today()->addDays(7);

        if ($targetDate->lt($today)) {
            return [
                'allowed' => false,
                'message' => 'Tanggal periksa tidak boleh di masa lampau.',
            ];
        }

        if ($targetDate->gt($maxDate)) {
            return [
                'allowed' => false,
                'message' => 'Pemesanan online hanya dapat dilakukan maksimal H-7 (7 hari ke depan).',
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Tanggal berada dalam rentang pemesanan yang diizinkan (H-7 s/d Hari H).',
        ];
    }

    /**
     * Map PHP Carbon dayOfWeekIso (1 = Monday, 7 = Sunday) to Indonesian day name.
     */
    public function getDayName(int $dayOfWeekIso): string
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $days[$dayOfWeekIso] ?? 'Senin';
    }

    /**
     * Check doctor's schedule and quota on a specific date.
     */
    public function getDoctorQuotaStatus(string $dokterUserId, string $tanggal): array
    {
        $targetCarbon = Carbon::parse($tanggal);
        $dayOfWeek = $targetCarbon->dayOfWeekIso; // 1 (Mon) - 7 (Sun)

        $dokter = Dokter::with(['poli', 'user'])->find($dokterUserId);

        if (!$dokter) {
            return [
                'available' => false,
                'message' => 'Data dokter tidak ditemukan dalam sistem.',
            ];
        }

        // Check if doctor has active practice schedule on this day
        $jadwal = Jadwal::where('jadwal_dokter_user_id', $dokterUserId)
            ->where('jadwal_hari', $dayOfWeek)
            ->first();

        if (!$jadwal) {
            return [
                'available' => false,
                'message' => "Dokter {$dokter->user->user_nama} tidak memiliki jadwal praktik pada hari " . $this->getDayName($dayOfWeek) . ".",
                'dokter' => $dokter,
            ];
        }

        if ($jadwal->jadwal_status !== 'aktif') {
            return [
                'available' => false,
                'message' => "Jadwal praktik dokter sedang berstatus {$jadwal->jadwal_status} pada hari " . $this->getDayName($dayOfWeek) . ".",
                'dokter' => $dokter,
                'jadwal' => $jadwal,
            ];
        }

        // Daily limit: uses jadwal_kuota if specified, otherwise dokter_kuotaharian (default 25)
        $maxQuota = $jadwal->jadwal_kuota ?: ($dokter->dokter_kuotaharian ?: 25);

        // Count non-cancelled appointments for this doctor on this date
        $bookedCount = Antrean::where('antrean_dokter_user_id', $dokterUserId)
            ->where('antrean_tanggal', $tanggal)
            ->where('antrean_status', '!=', 'batal')
            ->count();

        $remaining = max(0, $maxQuota - $bookedCount);
        $isAvailable = $remaining > 0;

        return [
            'available' => $isAvailable,
            'max_quota' => $maxQuota,
            'booked' => $bookedCount,
            'remaining' => $remaining,
            'message' => $isAvailable
                ? "Kuota periksa tersedia ({$remaining} slot tersisa dari kuota {$maxQuota})."
                : "Kuota dokter untuk tanggal ini telah penuh ({$bookedCount}/{$maxQuota}). Silakan pilih hari atau dokter lain.",
            'dokter' => $dokter,
            'jadwal' => $jadwal,
        ];
    }

    /**
     * Generate unique prefix for polyclinic queue numbers.
     */
    public function getPoliPrefix(int $poliId): string
    {
        $prefixes = [
            1 => 'UM',   // Poli Umum
            2 => 'GER',  // Poli Geriatri
            3 => 'KIA',  // Poli KIA / Anak
            4 => 'GIG',  // Poli Gigi & Mulut
            5 => 'KND',  // Poli Kandungan
        ];

        return $prefixes[$poliId] ?? 'A';
    }

    /**
     * Generate sequential queue number per polyclinic per date atomically.
     */
    public function allocateQueueNumber(int $poliId, string $tanggal): array
    {
        $prefix = $this->getPoliPrefix($poliId);

        // Row lock to prevent race conditions on queue numbering
        $latest = Antrean::where('antrean_poli_id', $poliId)
            ->where('antrean_tanggal', $tanggal)
            ->lockForUpdate()
            ->max('antrean_angka');

        $nextNumber = ($latest ?? 0) + 1;
        $queueCode = sprintf('%s-%03d', $prefix, $nextNumber);

        return [
            'antrean_nomor' => $queueCode,
            'antrean_angka' => $nextNumber,
        ];
    }

    /**
     * Generate secure QR Code token for electronic tickets.
     */
    public function generateQrCodeToken(): string
    {
        return 'KLN-QR-' . strtoupper(bin2hex(random_bytes(16)));
    }
}
