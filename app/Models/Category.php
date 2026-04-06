<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\tool;
use Illuminate\Database\Eloquent\Factories\HasFactory;  

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function tools() 
    {
        return $this->hasMany(tool::class);
    }
}
