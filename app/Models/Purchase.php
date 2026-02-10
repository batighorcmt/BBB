<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'purchase_no',
        'supplier_id',
        'purchase_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid',
        'due',
        'status',
        'notes',
        'created_by',
        'reference_no',
        'total_qty',
        'other_charges',
        'discount_type',
        'discount_amount',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'due' => 'decimal:2',
        'total_qty' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
