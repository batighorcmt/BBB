<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionItem extends Model
{
    protected $fillable = [
        'production_id',
        'quotation_item_id',
        'product_name',
        'size',
        'quantity',
        'envelope_weight',
        'envelope_price',
        'loop_weight',
        'loop_price',
        'print_cost',
        'sewing_cost',
        'price_per_piece',
        'total_price',
        'wastage_kg',
        'wastage_piece',
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function quotationItem()
    {
        return $this->belongsTo(QuotationItem::class);
    }
}
