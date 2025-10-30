<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $table = 'customer_contacts';
    public $timestamps = false;
    
    protected $fillable = [
        'customerid',
        'name',
        'position',
        'phone',
        'mobile',
        'email',
        'receivesinv'
    ];
}
