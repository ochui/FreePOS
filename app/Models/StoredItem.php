<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * StoredItem: Eloquent model representing an administrative inventory item.
 *
 */

class StoredItem extends Model
{
    protected $table = 'stored_items';
    public $timestamps = false;
    
    protected $fillable = [
        'data',
        'supplierid',
        'categoryid',
        'code',
        'name',
        'price'
    ];
}
