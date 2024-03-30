<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $primaryKey = 'id';

    protected $casts = [
        'products' => 'array',
        'transaction_date' => 'datetime',
    ];

    protected $fillable = [
        'products',
        'products_sold',
        'total',
        'transaction_date',
    ];
}
