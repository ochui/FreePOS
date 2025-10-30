<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxItem extends Model
{
    protected $table = 'tax_items';
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'altname',
        'type',
        'value',
        'multiplier'
    ];
}
