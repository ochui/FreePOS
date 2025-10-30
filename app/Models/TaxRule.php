<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    protected $table = 'tax_rules';
    public $timestamps = false;
    
    protected $fillable = [
        'data'
    ];
}
