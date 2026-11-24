<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekamMedis extends Model
{
    use HasFactory;

    protected $table = 'rekam_medis';
    protected $primaryKey = 'rekam_medis_id';

    protected $fillable = [
        'rekam_medis_antrean_id',
        'rekam_medis_pasien_user_id',
        'rekam_medis_dokter_user_id',
        'rekam_medis_poli_id',
        'rekam_medis_tanggal',
        'rekam_medis_keluhan',
        'rekam_medis_diagnosa',
        'rekam_medis_tanda_vital',
        'rekam_medis_tindakan',
        'rekam_medis_resep_obat',
    ];

    /**
     * Enkripsi Simetris AES-256 Sesuai Mandat UU PDP No. 27/2022
     * Nilai disimpan sebagai ciphertext di database dan didekripsi otomatis saat diakses.
     */
    protected $casts = [
        'rekam_medis_tanggal' => 'date:Y-m-d',
        'rekam_medis_keluhan' => 'encrypted',
        'rekam_medis_diagnosa' => 'encrypted',
        'rekam_medis_tanda_vital' => 'encrypted',
        'rekam_medis_tindakan' => 'encrypted',
        'rekam_medis_resep_obat' => 'encrypted',
    ];

    public function antrean()
    {
        return $this->belongsTo(Antrean::class, 'rekam_medis_antrean_id', 'antrean_id');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'rekam_medis_pasien_user_id', 'pasien_user_id');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'rekam_medis_dokter_user_id', 'dokter_user_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'rekam_medis_poli_id', 'poli_id');
    }
}
