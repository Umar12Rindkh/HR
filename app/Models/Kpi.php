<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jabatan',
        'desc',
        'bobot',
        'month',
        'year',
        'target',
        'realisasi',
        'skor'
    ];

    public function users()
{
    return $this->belongsTo(User::class, 'id_users');
}
}
