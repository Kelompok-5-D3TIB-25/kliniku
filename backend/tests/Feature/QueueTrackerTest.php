<?php

namespace Tests\Feature;

use App\Models\Antrean;
use App\Models\User;
use App\Services\JwtService;
use Carbon\Carbon;
use Tests\TestCase;

class QueueTrackerTest extends TestCase
{
    protected function getPatientToken(string $userId = 'USR-PAS-0001'): string
    {
        $user = User::findOrFail($userId);
        return app(JwtService::class)->generateToken($user);
    }

    public function test_tv_display_endpoint_returns_polyclinic_statuses(): void
    {
        $response = $this->getJson('/api/v1/display/tv');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'tanggal',
                'timestamp',
                'display_poli' => [
                    '*' => [
                        'poli_id',
                        'poli_nama',
                        'nomor_dipanggil',
                        'antrean_id',
                        'status',
                        'panggilan_ke',
                        'waktu_panggil',
                        'teks_tts',
                        'sisa_antrean',
                        'total_antrean',
                    ],
                ],
            ],
        ]);
    }

    public function test_live_tracking_for_queue_ticket(): void
    {
        $today = Carbon::today()->toDateString();

        // Create dummy antrean for testing
        $antrean = Antrean::create([
            'antrean_nomor' => 'UM-099',
            'antrean_angka' => 99,
            'antrean_pasien_user_id' => 'USR-PAS-0001',
            'antrean_dokter_user_id' => 'USR-DOC-001',
            'antrean_poli_id' => 1,
            'antrean_jadwal_id' => 1,
            'antrean_tanggal' => $today,
            'antrean_jenis' => 'online',
            'antrean_status' => 'menunggu',
            'antrean_qr_code' => 'KLN-QR-TESTLIVE99',
        ]);

        $response = $this->getJson("/api/v1/antrean/live/{$antrean->antrean_id}");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'tiket' => [
                    'antrean_id' => $antrean->antrean_id,
                    'antrean_nomor' => 'UM-099',
                    'antrean_status' => 'menunggu',
                ],
            ],
        ]);

        $response->assertJsonStructure([
            'data' => [
                'live_status' => [
                    'sisa_antrean_di_depan',
                    'total_menunggu_poli',
                    'estimasi_waktu_tunggu_menit',
                    'pesan',
                ],
            ],
        ]);

        $antrean->delete();
    }

    public function test_patient_can_view_my_schedule(): void
    {
        $token = $this->getPatientToken('USR-PAS-0001');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/antrean/saya');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => [
                    'antrean_id',
                    'antrean_nomor',
                    'antrean_angka',
                    'antrean_tanggal',
                    'antrean_status',
                    'poli',
                    'dokter',
                ],
            ],
        ]);
    }

    public function test_poli_hari_ini_endpoint(): void
    {
        $response = $this->getJson('/api/v1/antrean/poli/1/hari-ini');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'poli' => ['poli_id', 'poli_nama'],
                'tanggal',
                'total_antrean',
                'antrean',
            ],
        ]);
    }
}
