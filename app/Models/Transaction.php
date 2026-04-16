<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'order_id',
        'snap_token',
        'amount',
        'status',
        'payment_method',
        'midtrans_response'
    ];

    protected $casts = [
        'midtrans_response' => 'array',
        'amount' => 'integer',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}