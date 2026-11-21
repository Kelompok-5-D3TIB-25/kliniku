<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staf extends Model
{
    use HasFactory;

    protected $table = 'staf';
    protected $primaryKey = 'staf_user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'staf_user_id',
        'staf_role',
        'staf_jabatan',
        'staf_shiftnama',
        'staf_shiftmulai',
        'staf_shiftselesai',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'staf_user_id', 'user_id');
    }
}
