<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';
    public $timestamps = false;
    
    protected $fillable = [
        'ref',
        'type',
        'channel',
        'data',
        'userid',
        'deviceid',
        'locationid',
        'custid',
        'discount',
        'rounding',
        'cost',
        'total',
        'balance',
        'status',
        'processdt',
        'duedt',
        'dt'
    ];
}
