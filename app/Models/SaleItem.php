<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'production_item_id',
        'item_name',
        'size',
        'gsm',
        'color',
        'envelope_weight',
        'envelope_price',
        'loop_weight',
        'loop_price',
        'print_cost',
        'sewing_cost',
        'quantity',
        'price_per_piece',
        'total_price',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function productionItem()
    {
        return $this->belongsTo(ProductionItem::class);
    }
}
