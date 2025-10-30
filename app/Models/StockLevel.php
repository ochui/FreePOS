<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLevel extends Model
{
    protected $table = 'stock_levels';
    public $timestamps = false;
    
    protected $fillable = [
        'storeditemid',
        'locationid',
        'stocklevel',
        'dt'
    ];
}
