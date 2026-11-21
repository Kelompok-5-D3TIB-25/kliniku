<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';
    protected $primaryKey = 'jadwal_id';
    public $timestamps = false;

    protected $fillable = [
        'jadwal_dokter_user_id',
        'jadwal_hari',
        'jadwal_jammulai',
        'jadwal_jamselesai',
    ];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'jadwal_dokter_user_id', 'dokter_user_id');
    }
}
