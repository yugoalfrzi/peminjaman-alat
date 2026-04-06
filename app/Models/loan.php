<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class loan extends Model
{
    protected $guarded = [] ;

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
}
