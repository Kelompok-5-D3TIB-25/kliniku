<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    use HasFactory;

    protected $table = 'poli';
    protected $primaryKey = 'poli_id';
    public $timestamps = false;

    protected $fillable = [
        'poli_nama',
        'poli_keterangan',
    ];

    public function dokters()
    {
        return $this->hasMany(Dokter::class, 'dokter_poli_id', 'poli_id');
    }

    public function pasiens()
    {
        return $this->hasMany(Pasien::class, 'pasien_poli_id', 'poli_id');
    }
}
