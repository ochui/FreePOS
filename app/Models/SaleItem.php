<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $table = 'sale_items';
    public $timestamps = false;
    
    protected $fillable = [
        'saleid',
        'storeditemid',
        'saleitemid',
        'qty',
        'name',
        'description',
        'taxid',
        'tax',
        'tax_incl',
        'tax_total',
        'cost',
        'unit_original',
        'unit',
        'price',
        'refundqty'
    ];
}
