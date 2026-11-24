<?php

namespace Tests\Feature;

use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Jadwal;
use App\Models\User;
use App\Services\JwtService;
use Carbon\Carbon;
use Tests\TestCase;

class AdminJadwalAndLaporanTest extends TestCase
{
    protected function getStafToken(): string
    {
        $user = User::findOrFail('USR-ADM-001');
        return app(JwtService::class)->generateToken($user);
    }

    public function test_admin_can_view_doctor_schedules(): void
    {
        $token = $this->getStafToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/admin/jadwal');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'total',
                'jadwal' => [
                    '*' => [
                        'jadwal_id',
                        'dokter',
                        'hari_angka',
                        'hari_nama',
                        'jam_mulai',
                        'jam_selesai',
                        'kuota',
                        'status',
                    ],
                ],
            ],
        ]);
    }

    public function test_admin_can_create_update_and_delete_doctor_schedule(): void
    {
        $token = $this->getStafToken();

        // 1. Create schedule on day 6 (Sabtu) for USR-DOC-001
        // Ensure no conflicting Saturday schedule exists
        Jadwal::where('jadwal_dokter_user_id', 'USR-DOC-001')
            ->where('jadwal_hari', 6)
            ->delete();

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/admin/jadwal', [
                'dokter_user_id' => 'USR-DOC-001',
                'hari' => 6,
                'jam_mulai' => '09:00',
                'jam_selesai' => '13:00',
                'kuota' => 30,
                'status' => 'aktif',
            ]);

        $createResponse->assertStatus(201);
        $createResponse->assertJson([
            'status' => 'success',
            'data' => [
                'hari' => 'Sabtu',
                'kuota' => 30,
                'status' => 'aktif',
            ],
        ]);

        $jadwalId = $createResponse->json('data.jadwal_id');

        // 2. Update schedule: change kuota to 35 and status to 'cuti'
        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/v1/admin/jadwal/{$jadwalId}", [
                'kuota' => 35,
                'status' => 'cuti',
            ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson([
            'status' => 'success',
            'data' => [
                'kuota' => 35,
                'status' => 'cuti',
            ],
        ]);

        // 3. Delete schedule
        $deleteResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/v1/admin/jadwal/{$jadwalId}");

        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('jadwal', ['jadwal_id' => $jadwalId]);
    }

    public function test_admin_can_view_visit_report_and_metrics(): void
    {
        $token = $this->getStafToken();
        $today = Carbon::today()->toDateString();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/admin/laporan-kunjungan?tanggal_mulai={$today}&tanggal_selesai={$today}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'periode' => ['tanggal_mulai', 'tanggal_selesai'],
                'ringkasan' => [
                    'total_kunjungan',
                    'kunjungan_selesai',
                    'kunjungan_dilewati',
                    'kunjungan_batal',
                    'rata_rata_per_hari',
                ],
                'statistik' => [
                    'per_status',
                    'per_jenis',
                    'per_poli',
                    'per_tanggal',
                ],
                'daftar_kunjungan',
            ],
        ]);
    }

    public function test_admin_can_export_visit_report_as_csv(): void
    {
        $token = $this->getStafToken();
        $today = Carbon::today()->toDateString();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get("/api/v1/admin/laporan-kunjungan/export?tanggal_mulai={$today}&tanggal_selesai={$today}");

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=', $response->headers->get('Content-Disposition'));
    }
}
