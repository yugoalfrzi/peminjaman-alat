<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tool extends Model
{
    protected $guarded = [] ;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function loans()
    {
        return $this->hasMany(loan::class);
    }
}
