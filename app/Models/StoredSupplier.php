<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoredSupplier extends Model
{
    protected $table = 'stored_suppliers';
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'dt'
    ];
}
