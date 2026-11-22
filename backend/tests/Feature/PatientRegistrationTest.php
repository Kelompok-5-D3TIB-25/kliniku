<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PatientRegistrationTest extends TestCase
{
    public function test_can_fetch_polyclinic_list(): void
    {
        $response = $this->getJson('/api/polis');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['poli_id', 'poli_nama', 'poli_keterangan'],
            ],
        ]);
    }

    public function test_check_nik_detects_duplicate_and_available(): void
    {
        // Existing NIK from seeders (Patient 1)
        $existingNik = '3372020912254658';
        $responseDuplicate = $this->postJson('/api/check-nik', ['nik' => $existingNik]);
        $responseDuplicate->assertStatus(409);
        $responseDuplicate->assertJson(['available' => false]);

        // Non-existing valid NIK
        $newNik = '3372091508980005';
        $responseAvailable = $this->postJson('/api/check-nik', ['nik' => $newNik]);
        $responseAvailable->assertStatus(200);
        $responseAvailable->assertJson([
            'available' => true,
            'nik_info' => [
                'valid' => true,
                'gender' => 'Laki-laki',
                'birth_date' => '1998-08-15',
            ],
        ]);
    }

    public function test_registration_validation_fails_on_missing_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nik', 'nama', 'email', 'nowa', 'password', 'alamat']);
    }

    public function test_registration_validation_fails_on_invalid_nik_format(): void
    {
        $response = $this->postJson('/api/register', [
            'nik' => '12345', // not 16 digits
            'nama' => 'Testing Pasien',
            'email' => 'testing.pasien@example.com',
            'nowa' => '081299999999',
            'password' => 'Secret123!',
            'alamat' => 'Jl. Test No. 1',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nik']);
    }

    public function test_registration_validation_fails_on_duplicate_nik(): void
    {
        // Use an existing seeded patient's NIK
        $existingNik = '3372025801169929';

        $response = $this->postJson('/api/register', [
            'nik' => $existingNik,
            'nama' => 'Duplicate Patient Test',
            'email' => 'duplicate.patient@example.com',
            'nowa' => '081299999999',
            'password' => 'Secret123!',
            'alamat' => 'Jl. Test No. 2',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nik']);
    }

    public function test_successful_patient_registration_creates_records_and_rm_number(): void
    {
        $testNik = '3372085005990002'; // Day 50 -> Female, 1999-05-10

        // Clean up if exists from previous run
        User::where('user_nik', $testNik)->delete();

        $payload = [
            'nik' => $testNik,
            'nama' => 'Siti Nurhaliza Test',
            'email' => 'siti.nurhaliza.test@gmail.com',
            'nowa' => '081298765432',
            'password' => 'PasienSecret123!',
            'alamat' => 'Jl. Kemuning No. 15, Jebres, Surakarta',
            'goldarah' => 'B',
            'alergiobat' => 'Amoksisilin',
            'poli_id' => 3,
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'user_role' => 'pasien',
                'user_nama' => 'Siti Nurhaliza Test',
                'user_nik' => $testNik,
                'user_email' => 'siti.nurhaliza.test@gmail.com',
                'pasien_jeniskelamin' => 'Perempuan',
                'pasien_tgllahir' => '1999-05-10',
                'pasien_goldarah' => 'B',
                'pasien_alergiobat' => 'Amoksisilin',
            ],
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['user_id']);
        $this->assertStringStartsWith('USR-PAS-', $data['user_id']);
        $this->assertNotEmpty($data['pasien_norm']);
        $this->assertStringStartsWith('RM-', $data['pasien_norm']);

        // Assert database records
        $this->assertDatabaseHas('user', [
            'user_id' => $data['user_id'],
            'user_nik' => $testNik,
            'user_role' => 'pasien',
            'user_nama' => 'Siti Nurhaliza Test',
        ]);

        $this->assertDatabaseHas('pasien', [
            'pasien_user_id' => $data['user_id'],
            'pasien_norm' => $data['pasien_norm'],
            'pasien_jeniskelamin' => 'Perempuan',
            'pasien_alamat' => 'Jl. Kemuning No. 15, Jebres, Surakarta',
        ]);

        // Verify password hash
        $user = User::where('user_id', $data['user_id'])->first();
        $this->assertTrue(Hash::check('PasienSecret123!', $user->user_password));
    }
}
