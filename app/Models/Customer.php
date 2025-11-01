<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    public $timestamps = false;
    
    protected $fillable = [
        'email',
        'name',
        'phone',
        'mobile',
        'address',
        'suburb',
        'postcode',
        'state',
        'country',
        'notes',
        'googleid',
        'pass',
        'token',
        'activated',
        'disabled',
        'lastlogin',
        'dt'
    ];
}
