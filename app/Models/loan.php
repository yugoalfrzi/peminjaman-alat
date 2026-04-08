<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class loan extends Model
{
    protected $guarded = [] ;

     protected $casts = [
        'tanggal_kembali_rencana' => 'datetime',
        'tanggal_kembali_aktual' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }

    public function petugas()
    {
        return $this->belongsTo(user::class, 'petugas_id');
    }

    public function calculateDenda()
    {
        if ($this->tanggal_kembali_aktual || $this->tanggal_kembali_rencana) {
            return 0;
        }

        $hariTelat = max(0, $this->tanggal_kembali_aktual->diffInDays($this->tanggal_kembali_rencana));
        $dendaPerhari = 5000;

        return $hariTelat * $dendaPerhari;
    }
}
