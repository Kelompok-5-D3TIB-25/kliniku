<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'user_createdat';
    const UPDATED_AT = 'user_updatedat';

    protected $fillable = [
        'user_id',
        'user_role',
        'user_nama',
        'user_nik',
        'user_nowa',
        'user_email',
        'user_password',
        'user_status',
    ];

    protected $hidden = [
        'user_password',
    ];

    public function getAuthPassword()
    {
        return $this->user_password;
    }

    public function pasien()
    {
        return $this->hasOne(Pasien::class, 'pasien_user_id', 'user_id');
    }

    public function dokter()
    {
        return $this->hasOne(Dokter::class, 'dokter_user_id', 'user_id');
    }

    public function staf()
    {
        return $this->hasOne(Staf::class, 'staf_user_id', 'user_id');
    }
}
