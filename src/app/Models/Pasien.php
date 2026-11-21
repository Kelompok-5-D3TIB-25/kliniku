<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasien';
    protected $primaryKey = 'pasien_user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'pasien_user_id',
        'pasien_role',
        'pasien_norm',
        'pasien_jeniskelamin',
        'pasien_tgllahir',
        'pasien_alamat',
        'pasien_nobpjs',
        'pasien_goldarah',
        'pasien_alergiobat',
        'pasien_poli_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'pasien_user_id', 'user_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'pasien_poli_id', 'poli_id');
    }
}
