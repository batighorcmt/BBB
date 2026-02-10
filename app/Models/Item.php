<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'price',
        'cost',
        'stock_quantity',
        'unit',
        'description',
        'status',
        // Specifications
        'bag_type',
        'usage',
        'size',
        'capacity',
        'gsm',
        'fabric_color',
        'fabric_quality',
        'lamination',
        'print_type',
        'print_color_count',
        'print_sides',
        'brand_name',
        'design_file',
        'handle_type',
        'handle_color',
        'stitching_type',
        'bottom_finish',
    ];
}
