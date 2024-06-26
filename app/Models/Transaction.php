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
        'transaction_date' => 'datetime',
    ];

    protected $fillable = [
        'subtotal',
        'tax',
        'discount',
        'total',
        'paid',
        'change',
        'transaction_date',
    ];

    public function items() {
        return $this->hasMany(Item::class);
    }
}
