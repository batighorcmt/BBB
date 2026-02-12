<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class IdGenerator
{
    /**
     * Generate a sequential ID with a prefix and padding.
     *
     * @param string $model Full model class name
     * @param string $field The field name to check for the latest ID
     * @param string $prefix Prefix for the ID (e.g., 'SALE-')
     * @param int $padding Number of digits for the sequence
     * @return string
     */
    public static function generate($model, $field, $prefix, $padding = 5)
    {
        $query = $model::query();
        
        if (method_exists($model, 'withTrashed')) {
            $query->withTrashed();
        }
        
        $latest = $query->orderBy('id', 'desc')->first();
        
        $nextId = $latest ? $latest->id + 1 : 1;
        
        return $prefix . str_pad($nextId, $padding, '0', STR_PAD_LEFT);
    }
}
