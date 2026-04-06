<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\support\Facades\Auth;

class ActivityLog extends Model
{
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    // fungsi helper untuk mencatat aktivitas
    public static function record($action, $desc = null ) {
        return self::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $desc,
        ]);
    }
}
