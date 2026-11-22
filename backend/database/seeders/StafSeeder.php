<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StafSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $password = Hash::make('Admin123!');

        $users = [
            [
                'user_id' => 'USR-ADM-001',
                'user_role' => 'staf',
                'user_nama' => 'Rina Wijayanti, A.Md.RMIK',
                'user_nik' => '3372045508950001',
                'user_nowa' => '081334567801',
                'user_email' => 'rina.admin@kliniku.id',
                'user_password' => $password,
                'user_status' => 'aktif',
                'user_createdat' => $now,
                'user_updatedat' => $now,
            ],
            [
                'user_id' => 'USR-ADM-002',
                'user_role' => 'staf',
                'user_nama' => 'Dedi Kurniawan, S.Kom',
                'user_nik' => '3372051210920002',
                'user_nowa' => '081334567802',
                'user_email' => 'dedi.admin@kliniku.id',
                'user_password' => $password,
                'user_status' => 'aktif',
                'user_createdat' => $now,
                'user_updatedat' => $now,
            ],
        ];

        DB::table('user')->upsert($users, ['user_id'], ['user_role', 'user_nama', 'user_nik', 'user_nowa', 'user_email', 'user_password', 'user_status', 'user_updatedat']);

        $stafs = [
            [
                'staf_user_id' => 'USR-ADM-001',
                'staf_role' => 'staf',
                'staf_jabatan' => 'Admin Loket (Perekam Medis)',
                'staf_shiftnama' => 'Pagi',
                'staf_shiftmulai' => '07:30:00',
                'staf_shiftselesai' => '14:30:00',
            ],
            [
                'staf_user_id' => 'USR-ADM-002',
                'staf_role' => 'staf',
                'staf_jabatan' => 'Admin Loket (IT & Operasional)',
                'staf_shiftnama' => 'Siang',
                'staf_shiftmulai' => '13:30:00',
                'staf_shiftselesai' => '20:30:00',
            ],
        ];

        DB::table('staf')->upsert($stafs, ['staf_user_id'], ['staf_role', 'staf_jabatan', 'staf_shiftnama', 'staf_shiftmulai', 'staf_shiftselesai']);
    }
}
