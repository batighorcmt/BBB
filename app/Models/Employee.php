<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_code',
        'name',
        'phone',
        'email',
        'address',
        'designation',
        'employment_type',
        'salary',
        'joining_date',
        'leaving_date',
        'status',
        'photo',
        'notes',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'leaving_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
