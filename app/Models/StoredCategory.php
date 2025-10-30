<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoredCategory extends Model
{
    protected $table = 'stored_categories';
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'dt'
    ];
}
