<?php

namespace Tests\Feature;

use App\Mail\QueueCalledMail;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Jadwal;
use App\Models\User;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LoketTest extends TestCase
{
    protected function getStafToken(): string
    {
        $user = User::findOrFail('USR-ADM-001');
        return app(JwtService::class)->generateToken($user);
    }

    protected function getPatientToken(): string
    {
        $user = User::findOrFail('USR-PAS-0001');
        return app(JwtService::class)->generateToken($user);
    }

    protected function ensureDoctorHasTodaySchedule(string $dokterUserId = 'USR-DOC-001'): void
    {
        $todayDayOfWeek = Carbon::today()->dayOfWeekIso;
        Jadwal::updateOrCreate(
            [
                'jadwal_dokter_user_id' => $dokterUserId,
                'jadwal_hari' => $todayDayOfWeek,
            ],
            [
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '16:00:00',
                'jadwal_kuota' => 25,
                'jadwal_status' => 'aktif',
            ]
        );
    }

    public function test_patient_cannot_access_loket_endpoints(): void
    {
        $token = $this->getPatientToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/loket/checkin-qr', ['qr_code' => 'ANY-CODE']);

        $response->assertStatus(403);
    }

    public function test_qr_checkin_success_on_today_booking(): void
    {
        $stafToken = $this->getStafToken();
        $today = Carbon::today()->toDateString();
        $qrCode = 'KLN-QR-TESTCHECKIN' . uniqid();

        $antrean = Antrean::create([
            'antrean_nomor' => 'UM-010',
            'antrean_angka' => 10,
            'antrean_pasien_user_id' => 'USR-PAS-0001',
            'antrean_dokter_user_id' => 'USR-DOC-001',
            'antrean_poli_id' => 1,
            'antrean_jadwal_id' => 1,
            'antrean_tanggal' => $today,
            'antrean_jenis' => 'online',
            'antrean_status' => 'menunggu_checkin',
            'antrean_qr_code' => $qrCode,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $stafToken)
            ->postJson('/api/v1/loket/checkin-qr', [
                'qr_code' => $qrCode,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'antrean_nomor' => 'UM-010',
                'antrean_status' => 'menunggu',
            ],
        ]);

        $this->assertDatabaseHas('antrean', [
            'antrean_id' => $antrean->antrean_id,
            'antrean_status' => 'menunggu',
        ]);

        // Attempt duplicate check-in
        $duplicate = $this->withHeader('Authorization', 'Bearer ' . $stafToken)
            ->postJson('/api/v1/loket/checkin-qr', [
                'qr_code' => $qrCode,
            ]);

        $duplicate->assertStatus(422);
        $this->assertStringContainsString('sudah melakukan check-in', $duplicate->json('message'));

        $antrean->delete();
    }

    public function test_qr_checkin_fails_if_appointment_date_is_not_today(): void
    {
        $stafToken = $this->getStafToken();
        $tomorrow = Carbon::tomorrow()->toDateString();
        $qrCode = 'KLN-QR-TESTTOMORROW' . uniqid();

        $antrean = Antrean::create([
            'antrean_nomor' => 'UM-011',
            'antrean_angka' => 11,
            'antrean_pasien_user_id' => 'USR-PAS-0001',
            'antrean_dokter_user_id' => 'USR-DOC-001',
            'antrean_poli_id' => 1,
            'antrean_jadwal_id' => 1,
            'antrean_tanggal' => $tomorrow,
            'antrean_jenis' => 'online',
            'antrean_status' => 'menunggu_checkin',
            'antrean_qr_code' => $qrCode,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $stafToken)
            ->postJson('/api/v1/loket/checkin-qr', [
                'qr_code' => $qrCode,
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Check-in gagal', $response->json('message'));

        $antrean->delete();
    }

    public function test_pendaftaran_walkin_creates_ticket_and_patient(): void
    {
        $this->ensureDoctorHasTodaySchedule('USR-DOC-001');
        $stafToken = $this->getStafToken();

        $uniqueNik = '337202' . str_pad((string) rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);

        $response = $this->withHeader('Authorization', 'Bearer ' . $stafToken)
            ->postJson('/api/v1/loket/pendaftaran-walkin', [
                'nik' => $uniqueNik,
                'nama' => 'Pasien Walk-in Baru',
                'nowa' => '081299887766',
                'email' => 'walkin.baru@example.com',
                'jeniskelamin' => 'Laki-laki',
                'tgllahir' => '1995-05-15',
                'alamat' => 'Jl. Slamet Riyadi No. 100, Surakarta',
                'dokter_user_id' => 'USR-DOC-001',
                'keluhan' => 'Pusing dan mual',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'antrean_status' => 'menunggu',
                'antrean_jenis' => 'walkin',
            ],
        ]);

        $antreanId = $response->json('data.antrean_id');
        $this->assertNotNull($antreanId);

        // Clean up
        Antrean::where('antrean_id', $antreanId)->delete();
        User::where('user_nik', $uniqueNik)->delete();
    }

    public function test_queue_calling_sends_email_notification_and_tv_tts(): void
    {
        Mail::fake();

        $stafToken = $this->getStafToken();
        $today = Carbon::today()->toDateString();

        $antrean = Antrean::create([
            'antrean_nomor' => 'UM-012',
            'antrean_angka' => 12,
            'antrean_pasien_user_id' => 'USR-PAS-0001',
            'antrean_dokter_user_id' => 'USR-DOC-001',
            'antrean_poli_id' => 1,
            'antrean_jadwal_id' => 1,
            'antrean_tanggal' => $today,
            'antrean_jenis' => 'walkin',
            'antrean_status' => 'menunggu',
            'antrean_qr_code' => 'KLN-QR-TESTCALL12',
        ]);

        // 1. Panggil
        $callResponse = $this->withHeader('Authorization', 'Bearer ' . $stafToken)
            ->postJson("/api/v1/loket/antrean/{$antrean->antrean_id}/panggil");

        $callResponse->assertStatus(200);
        $callResponse->assertJson([
            'status' => 'success',
            'data' => [
                'antrean_status' => 'dipanggil',
                'antrean_panggilan_ke' => 1,
            ],
        ]);

        Mail::assertSent(QueueCalledMail::class);

        // 2. Panggil Ulang (Recall)
        $recallResponse = $this->withHeader('Authorization', 'Bearer ' . $stafToken)
            ->postJson("/api/v1/loket/antrean/{$antrean->antrean_id}/panggil-ulang");

        $recallResponse->assertStatus(200);
        $recallResponse->assertJson([
            'status' => 'success',
            'data' => [
                'antrean_panggilan_ke' => 2,
            ],
        ]);

        // 3. Lewati (Skip)
        $skipResponse = $this->withHeader('Authorization', 'Bearer ' . $stafToken)
            ->postJson("/api/v1/loket/antrean/{$antrean->antrean_id}/lewati");

        $skipResponse->assertStatus(200);
        $skipResponse->assertJson([
            'status' => 'success',
            'data' => [
                'antrean_status' => 'dilewati',
            ],
        ]);

        // 4. Layani
        $layaniResponse = $this->withHeader('Authorization', 'Bearer ' . $stafToken)
            ->postJson("/api/v1/loket/antrean/{$antrean->antrean_id}/layani");

        $layaniResponse->assertStatus(200);
        $layaniResponse->assertJson([
            'status' => 'success',
            'data' => [
                'antrean_status' => 'sedang_diperiksa',
            ],
        ]);

        $antrean->delete();
    }
}
