<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'work_hours',
        'status',
        'notes',
        'check_in_photo',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_address',
        'check_out_photo',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_address',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
    ];



    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
