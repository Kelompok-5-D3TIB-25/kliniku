<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antrean extends Model
{
    use HasFactory;

    protected $table = 'antrean';
    protected $primaryKey = 'antrean_id';

    protected $fillable = [
        'antrean_nomor',
        'antrean_angka',
        'antrean_pasien_user_id',
        'antrean_dokter_user_id',
        'antrean_poli_id',
        'antrean_jadwal_id',
        'antrean_tanggal',
        'antrean_jenis',
        'antrean_status',
        'antrean_qr_code',
        'antrean_waktu_checkin',
        'antrean_waktu_panggil',
        'antrean_waktu_selesai',
        'antrean_panggilan_ke',
        'antrean_catatan',
    ];

    protected $casts = [
        'antrean_tanggal' => 'date:Y-m-d',
        'antrean_waktu_checkin' => 'datetime',
        'antrean_waktu_panggil' => 'datetime',
        'antrean_waktu_selesai' => 'datetime',
        'antrean_angka' => 'integer',
        'antrean_panggilan_ke' => 'integer',
    ];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'antrean_pasien_user_id', 'pasien_user_id');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'antrean_dokter_user_id', 'dokter_user_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'antrean_poli_id', 'poli_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'antrean_jadwal_id', 'jadwal_id');
    }

    public function rekamMedis()
    {
        return $this->hasOne(RekamMedis::class, 'rekam_medis_antrean_id', 'antrean_id');
    }
}
