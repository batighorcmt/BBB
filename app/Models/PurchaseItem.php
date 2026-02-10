<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'item_id',
        'quantity',
        'unit_price',
        'total',
        'item_discount',
        'item_tax',
        'net_unit_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'item_discount' => 'decimal:2',
        'item_tax' => 'decimal:2',
        'net_unit_cost' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
