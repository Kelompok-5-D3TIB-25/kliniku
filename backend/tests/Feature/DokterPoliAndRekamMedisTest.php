<?php

namespace Tests\Feature;

use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\RekamMedis;
use App\Models\User;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DokterPoliAndRekamMedisTest extends TestCase
{
    protected function getDoctorToken(): string
    {
        $user = User::findOrFail('USR-DOC-001');
        return app(JwtService::class)->generateToken($user);
    }

    protected function getPatientToken(string $userId = 'USR-PAS-0001'): string
    {
        $user = User::findOrFail($userId);
        return app(JwtService::class)->generateToken($user);
    }

    public function test_doctor_can_view_today_assigned_queue(): void
    {
        $token = $this->getDoctorToken();
        $today = Carbon::today()->toDateString();

        $antrean = Antrean::create([
            'antrean_nomor' => 'UM-020',
            'antrean_angka' => 20,
            'antrean_pasien_user_id' => 'USR-PAS-0001',
            'antrean_dokter_user_id' => 'USR-DOC-001',
            'antrean_poli_id' => 1,
            'antrean_jadwal_id' => 1,
            'antrean_tanggal' => $today,
            'antrean_jenis' => 'online',
            'antrean_status' => 'menunggu',
            'antrean_qr_code' => 'KLN-QR-TESTDOC20',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/dokter/antrean-hari-ini');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'dokter',
                'tanggal',
                'total',
                'antrean' => [
                    '*' => [
                        'antrean_id',
                        'antrean_nomor',
                        'antrean_status',
                        'pasien',
                    ],
                ],
            ],
        ]);

        $antrean->delete();
    }

    public function test_doctor_can_store_examination_with_aes_256_encryption_and_finish_queue(): void
    {
        $token = $this->getDoctorToken();
        $today = Carbon::today()->toDateString();

        $antrean = Antrean::create([
            'antrean_nomor' => 'UM-021',
            'antrean_angka' => 21,
            'antrean_pasien_user_id' => 'USR-PAS-0001',
            'antrean_dokter_user_id' => 'USR-DOC-001',
            'antrean_poli_id' => 1,
            'antrean_jadwal_id' => 1,
            'antrean_tanggal' => $today,
            'antrean_jenis' => 'online',
            'antrean_status' => 'sedang_diperiksa',
            'antrean_qr_code' => 'KLN-QR-TESTDOC21',
        ]);

        $secretDiagnosis = 'J06.9 - Acute upper respiratory infection, unspecified';
        $secretPrescription = 'Amoxicillin 500mg 3x1, Paracetamol 500mg 3x1';

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/dokter/rekam-medis', [
                'antrean_id' => $antrean->antrean_id,
                'keluhan' => 'Demam menggigil, batuk berdahak, pilek 4 hari',
                'diagnosa' => $secretDiagnosis,
                'tanda_vital' => 'TD: 120/80 mmHg, Suhu: 38.2 C, Nadi: 88 bpm',
                'tindakan' => 'Pemeriksaan fisik toraks dan auskultasi paru',
                'resep_obat' => $secretPrescription,
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'antrean_status' => 'selesai',
                'diagnosa' => $secretDiagnosis,
            ],
        ]);

        $rmId = $response->json('data.rekam_medis_id');

        // Verify Antrean status updated to 'selesai'
        $this->assertDatabaseHas('antrean', [
            'antrean_id' => $antrean->antrean_id,
            'antrean_status' => 'selesai',
        ]);

        // VERIFY AES-256 ENCRYPTION DIRECTLY IN RAW DATABASE QUERY:
        // Raw values in the DB table must NOT contain plaintext strings!
        $rawRecord = DB::table('rekam_medis')->where('rekam_medis_id', $rmId)->first();
        $this->assertNotNull($rawRecord);
        $this->assertStringNotContainsString($secretDiagnosis, $rawRecord->rekam_medis_diagnosa, 'Raw DB field must be encrypted!');
        $this->assertStringNotContainsString($secretPrescription, $rawRecord->rekam_medis_resep_obat, 'Raw DB field must be encrypted!');

        // Patient can view their own medical history
        $patientToken = $this->getPatientToken('USR-PAS-0001');
        $patientResponse = $this->withHeader('Authorization', 'Bearer ' . $patientToken)
            ->getJson('/api/v1/pasien/riwayat-medis');

        $patientResponse->assertStatus(200);
        $this->assertStringContainsString($secretDiagnosis, json_encode($patientResponse->json()));

        // Single detail
        $detailResponse = $this->withHeader('Authorization', 'Bearer ' . $patientToken)
            ->getJson("/api/v1/pasien/riwayat-medis/{$rmId}");

        $detailResponse->assertStatus(200);
        $detailResponse->assertJson([
            'status' => 'success',
            'data' => [
                'rekam_medis_id' => $rmId,
                'diagnosa' => $secretDiagnosis,
            ],
        ]);

        // Clean up
        RekamMedis::where('rekam_medis_id', $rmId)->delete();
        $antrean->delete();
    }
}
