<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmedMail;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Jadwal;
use App\Models\User;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingTest extends TestCase
{
    protected function getPatientToken(string $userId = 'USR-PAS-0001'): string
    {
        $user = User::findOrFail($userId);
        return app(JwtService::class)->generateToken($user);
    }

    protected function getNextPracticeDate(string $dokterUserId): string
    {
        $jadwalDays = Jadwal::where('jadwal_dokter_user_id', $dokterUserId)
            ->where('jadwal_status', 'aktif')
            ->pluck('jadwal_hari')
            ->toArray();

        for ($i = 0; $i <= 7; $i++) {
            $date = Carbon::today()->addDays($i);
            if (in_array($date->dayOfWeekIso, $jadwalDays)) {
                return $date->toDateString();
            }
        }

        return Carbon::today()->toDateString();
    }

    public function test_get_dokter_list(): void
    {
        $response = $this->getJson('/api/v1/booking/dokter');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => [
                    'dokter_user_id',
                    'nama',
                    'spesialisasi',
                    'sip',
                    'kuota_harian',
                    'poli',
                    'jadwal',
                ],
            ],
        ]);
    }

    public function test_cek_kuota_success(): void
    {
        $date = $this->getNextPracticeDate('USR-DOC-001');

        $response = $this->postJson('/api/v1/booking/cek-kuota', [
            'dokter_user_id' => 'USR-DOC-001',
            'tanggal' => $date,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'tanggal' => $date,
                'available' => true,
            ],
        ]);
    }

    public function test_cek_kuota_rejects_past_or_far_future_dates(): void
    {
        // Past date
        $pastDate = Carbon::yesterday()->toDateString();
        $response = $this->postJson('/api/v1/booking/cek-kuota', [
            'dokter_user_id' => 'USR-DOC-001',
            'tanggal' => $pastDate,
        ]);
        $response->assertStatus(422);

        // Date > H-7 (e.g. 10 days ahead)
        $futureDate = Carbon::today()->addDays(10)->toDateString();
        $response2 = $this->postJson('/api/v1/booking/cek-kuota', [
            'dokter_user_id' => 'USR-DOC-001',
            'tanggal' => $futureDate,
        ]);
        $response2->assertStatus(422);
    }

    public function test_patient_can_create_booking_and_receives_confirmation_email(): void
    {
        Mail::fake();

        $token = $this->getPatientToken('USR-PAS-0001');
        $date = $this->getNextPracticeDate('USR-DOC-001');

        // Clean previous antrean for test isolation
        Antrean::where('antrean_pasien_user_id', 'USR-PAS-0001')
            ->where('antrean_tanggal', $date)
            ->delete();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/booking', [
                'dokter_user_id' => 'USR-DOC-001',
                'tanggal' => $date,
                'keluhan' => 'Demam dan flu selama 3 hari',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'antrean_id',
                'antrean_nomor',
                'antrean_angka',
                'antrean_tanggal',
                'antrean_status',
                'antrean_qr_code',
                'poli',
                'dokter',
                'jadwal',
            ],
        ]);

        $bookingData = $response->json('data');
        $this->assertStringStartsWith('UM-', $bookingData['antrean_nomor']);
        $this->assertStringStartsWith('KLN-QR-', $bookingData['antrean_qr_code']);

        // Verify email was dispatched
        Mail::assertSent(BookingConfirmedMail::class, function ($mail) use ($bookingData) {
            return $mail->antrean->antrean_id === $bookingData['antrean_id'];
        });

        // Test viewing ticket
        $tiketResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/booking/{$bookingData['antrean_id']}/tiket");

        $tiketResponse->assertStatus(200);
        $tiketResponse->assertJson([
            'status' => 'success',
            'data' => [
                'antrean_id' => $bookingData['antrean_id'],
                'antrean_nomor' => $bookingData['antrean_nomor'],
                'antrean_status' => 'menunggu_checkin',
            ],
        ]);

        // Test cancel booking
        $cancelResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/booking/{$bookingData['antrean_id']}/batal");

        $cancelResponse->assertStatus(200);
        $cancelResponse->assertJson([
            'status' => 'success',
            'data' => [
                'antrean_status' => 'batal',
            ],
        ]);
    }

    public function test_booking_fails_on_duplicate_patient_booking_same_day(): void
    {
        Mail::fake();

        $token = $this->getPatientToken('USR-PAS-0001');
        $date = $this->getNextPracticeDate('USR-DOC-001');

        // Ensure one active booking exists
        Antrean::where('antrean_pasien_user_id', 'USR-PAS-0001')
            ->where('antrean_tanggal', $date)
            ->delete();

        $first = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/booking', [
                'dokter_user_id' => 'USR-DOC-001',
                'tanggal' => $date,
                'keluhan' => 'Pemeriksaan rutin',
            ]);
        $first->assertStatus(201);

        // Attempt second booking on the same date
        $second = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/booking', [
                'dokter_user_id' => 'USR-DOC-001',
                'tanggal' => $date,
                'keluhan' => 'Pemeriksaan kedua',
            ]);

        $second->assertStatus(422);
        $this->assertStringContainsString('sudah memiliki antrean aktif', $second->json('message'));
    }
}
