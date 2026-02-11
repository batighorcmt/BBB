<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_no',
        'customer_id',
        'quotation_date',
        'valid_until',
        'subtotal',
        'discount',
        'tax',
        'total',
        'advance_amount',
        'status', // pending, approved, rejected, converted
        'terms_conditions',
        'notes',
        'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function production()
    {
        return $this->hasOne(Production::class);
    }
}
