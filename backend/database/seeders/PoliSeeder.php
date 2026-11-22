<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('poli')->insertOrIgnore([
            ['poli_nama' => 'Poli Umum', 'poli_keterangan' => null],
            ['poli_nama' => 'Poli Umum & Geriatri (Lansia)', 'poli_keterangan' => null],
            ['poli_nama' => 'Poli KIA / Anak', 'poli_keterangan' => null],
            ['poli_nama' => 'Poli Gigi & Mulut', 'poli_keterangan' => null],
            ['poli_nama' => 'Poli Kandungan & Kebidanan', 'poli_keterangan' => null],
        ]);
    }
}
