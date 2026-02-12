<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'sale_no',
        'customer_id',
        'production_id',
        'type',
        'subtotal',
        'discount',
        'tax',
        'other_costs',
        'grand_total',
        'advance_adjusted',
        'paid_amount',
        'due_amount',
        'status',
        'note',
        'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
