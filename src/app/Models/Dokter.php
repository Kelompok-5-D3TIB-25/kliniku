<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    use HasFactory;

    protected $table = 'dokter';
    protected $primaryKey = 'dokter_user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dokter_user_id',
        'dokter_role',
        'dokter_spesialisasi',
        'dokter_sip',
        'dokter_kuotaharian',
        'dokter_poli_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'dokter_user_id', 'user_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'dokter_poli_id', 'poli_id');
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'jadwal_dokter_user_id', 'dokter_user_id');
    }
}
