<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $password = Hash::make('Dokter123!');

        $users = [
            [
                'user_id' => 'USR-DOC-001',
                'user_role' => 'dokter',
                'user_nama' => 'dr. Andi Pratama, Sp.PD',
                'user_nik' => '3372011205820001',
                'user_nowa' => '081225678901',
                'user_email' => 'dr.andi@kliniku.id',
                'user_password' => $password,
                'user_status' => 'aktif',
                'user_createdat' => $now,
                'user_updatedat' => $now,
            ],
            [
                'user_id' => 'USR-DOC-002',
                'user_role' => 'dokter',
                'user_nama' => 'drg. Siti Nurhaliza',
                'user_nik' => '3372024809880002',
                'user_nowa' => '081225678902',
                'user_email' => 'drg.siti@kliniku.id',
                'user_password' => $password,
                'user_status' => 'aktif',
                'user_createdat' => $now,
                'user_updatedat' => $now,
            ],
            [
                'user_id' => 'USR-DOC-003',
                'user_role' => 'dokter',
                'user_nama' => 'dr. Budi Santoso, Sp.A',
                'user_nik' => '3372031503790003',
                'user_nowa' => '081225678903',
                'user_email' => 'dr.budi@kliniku.id',
                'user_password' => $password,
                'user_status' => 'aktif',
                'user_createdat' => $now,
                'user_updatedat' => $now,
            ],
            [
                'user_id' => 'USR-DOC-004',
                'user_role' => 'dokter',
                'user_nama' => 'dr. Ratna Kartika, Sp.OG',
                'user_nik' => '3372046207850004',
                'user_nowa' => '081225678904',
                'user_email' => 'dr.ratna@kliniku.id',
                'user_password' => $password,
                'user_status' => 'aktif',
                'user_createdat' => $now,
                'user_updatedat' => $now,
            ],
            [
                'user_id' => 'USR-DOC-005',
                'user_role' => 'dokter',
                'user_nama' => 'dr. Hendra Gunawan, Sp.PD',
                'user_nik' => '3372051811860005',
                'user_nowa' => '081225678905',
                'user_email' => 'dr.hendra@kliniku.id',
                'user_password' => $password,
                'user_status' => 'aktif',
                'user_createdat' => $now,
                'user_updatedat' => $now,
            ],
        ];

        DB::table('user')->upsert($users, ['user_id'], ['user_role', 'user_nama', 'user_nik', 'user_nowa', 'user_email', 'user_password', 'user_status', 'user_updatedat']);

        $dokters = [
            [
                'dokter_user_id' => 'USR-DOC-001',
                'dokter_role' => 'dokter',
                'dokter_spesialisasi' => 'Penyakit Dalam',
                'dokter_sip' => '503/SIP.01/DKS/2023',
                'dokter_kuotaharian' => 25,
                'dokter_poli_id' => 1,
            ],
            [
                'dokter_user_id' => 'USR-DOC-002',
                'dokter_role' => 'dokter',
                'dokter_spesialisasi' => 'Gigi & Mulut',
                'dokter_sip' => '503/SIP.02/DKS/2024',
                'dokter_kuotaharian' => 25,
                'dokter_poli_id' => 4,
            ],
            [
                'dokter_user_id' => 'USR-DOC-003',
                'dokter_role' => 'dokter',
                'dokter_spesialisasi' => 'Kesehatan Anak',
                'dokter_sip' => '503/SIP.03/DKS/2022',
                'dokter_kuotaharian' => 25,
                'dokter_poli_id' => 3,
            ],
            [
                'dokter_user_id' => 'USR-DOC-004',
                'dokter_role' => 'dokter',
                'dokter_spesialisasi' => 'Kandungan & Kebidanan',
                'dokter_sip' => '503/SIP.04/DKS/2023',
                'dokter_kuotaharian' => 25,
                'dokter_poli_id' => 5,
            ],
            [
                'dokter_user_id' => 'USR-DOC-005',
                'dokter_role' => 'dokter',
                'dokter_spesialisasi' => 'Geriatri (Lansia)',
                'dokter_sip' => '503/SIP.05/DKS/2024',
                'dokter_kuotaharian' => 25,
                'dokter_poli_id' => 2,
            ],
        ];

        DB::table('dokter')->upsert($dokters, ['dokter_user_id'], ['dokter_role', 'dokter_spesialisasi', 'dokter_sip', 'dokter_kuotaharian', 'dokter_poli_id']);

        $jadwals = [
            [
                'jadwal_dokter_user_id' => 'USR-DOC-001',
                'jadwal_hari' => 1,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-001',
                'jadwal_hari' => 2,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-001',
                'jadwal_hari' => 3,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-001',
                'jadwal_hari' => 4,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-001',
                'jadwal_hari' => 5,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-002',
                'jadwal_hari' => 1,
                'jadwal_jammulai' => '13:00:00',
                'jadwal_jamselesai' => '17:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-002',
                'jadwal_hari' => 2,
                'jadwal_jammulai' => '13:00:00',
                'jadwal_jamselesai' => '17:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-002',
                'jadwal_hari' => 3,
                'jadwal_jammulai' => '13:00:00',
                'jadwal_jamselesai' => '17:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-002',
                'jadwal_hari' => 4,
                'jadwal_jammulai' => '13:00:00',
                'jadwal_jamselesai' => '17:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-003',
                'jadwal_hari' => 1,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-003',
                'jadwal_hari' => 3,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-003',
                'jadwal_hari' => 5,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-004',
                'jadwal_hari' => 2,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-004',
                'jadwal_hari' => 4,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-004',
                'jadwal_hari' => 6,
                'jadwal_jammulai' => '08:00:00',
                'jadwal_jamselesai' => '12:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-005',
                'jadwal_hari' => 1,
                'jadwal_jammulai' => '13:00:00',
                'jadwal_jamselesai' => '17:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-005',
                'jadwal_hari' => 2,
                'jadwal_jammulai' => '13:00:00',
                'jadwal_jamselesai' => '17:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-005',
                'jadwal_hari' => 3,
                'jadwal_jammulai' => '13:00:00',
                'jadwal_jamselesai' => '17:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-005',
                'jadwal_hari' => 4,
                'jadwal_jammulai' => '13:00:00',
                'jadwal_jamselesai' => '17:00:00',
            ],
            [
                'jadwal_dokter_user_id' => 'USR-DOC-005',
                'jadwal_hari' => 5,
                'jadwal_jammulai' => '13:00:00',
                'jadwal_jamselesai' => '17:00:00',
            ],
        ];

        DB::table('jadwal')->upsert($jadwals, ['jadwal_dokter_user_id', 'jadwal_hari', 'jadwal_jammulai'], ['jadwal_jamselesai']);
    }
}
