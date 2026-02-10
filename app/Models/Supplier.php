<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'supplier_code',
        'name',
        'company_name',
        'phone',
        'email',
        'address',
        'city',
        'opening_balance',
        'status',
        'notes',
    ];

    protected $appends = ['total_due'];

    protected $casts = [
        'opening_balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function getTotalDueAttribute()
    {
        // Total Due = Opening Balance + (Sum of Purchase Dues) 
        // Note: Purchase 'due' column already stores the due amount for that purchase.
        // Make sure to only count non-cancelled purchases if needed, but 'due' should reflect status.
        return $this->opening_balance + $this->purchases()->sum('due');
    }
}
