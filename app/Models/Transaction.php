<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'location_id',
        'member_card_id',
        'entry_time',
        'exit_time',
        'duration_hours',
        'base_rate',
        'benefit',
        'total_amount',
        'status',
        'payment_method',
        'voucher_code',
        'paid_at',
    ];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
        'paid_at' => 'datetime',
        'duration_hours' => 'float',
        'base_rate' => 'float',
        'benefit' => 'float',
        'total_amount' => 'float',
    ];
}
