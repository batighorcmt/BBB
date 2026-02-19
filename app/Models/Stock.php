<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'item_id',
        'quantity',
        'average_cost',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
