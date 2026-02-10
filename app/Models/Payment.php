<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_no',
        'payable_id',
        'payable_type',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'status',
        'collected_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
