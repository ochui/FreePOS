<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $table = 'sale_payments';
    public $timestamps = false;
    
    protected $fillable = [
        'saleid',
        'method',
        'amount',
        'processdt'
    ];
}
